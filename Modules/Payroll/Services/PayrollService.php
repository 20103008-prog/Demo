<?php

namespace Modules\Payroll\Services;

use App\Models\Bonus;
use App\Models\Increment;
use App\Models\PayrollSetting;
use App\Models\Payslip;
use App\Models\User;
use Carbon\Carbon;

class PayrollService
{
    public function __construct(
        protected TaxService $tax,
        protected PfService $pf,
        protected LoanService $loans,
        protected AttendanceService $attendance,
    ) {}

    public function salaryComponents(float $grossSalary): array
    {
        $basicPct = PayrollSetting::getFloat('salary_basic_pct', 60);
        $hraPct = PayrollSetting::getFloat('salary_hra_pct', 20);
        $daPct = PayrollSetting::getFloat('salary_da_pct', 10);
        $allowPct = PayrollSetting::getFloat('salary_allowance_pct', 10);

        return [
            'basic' => round($grossSalary * ($basicPct / 100), 2),
            'hra' => round($grossSalary * ($hraPct / 100), 2),
            'da' => round($grossSalary * ($daPct / 100), 2),
            'allowances' => round($grossSalary * ($allowPct / 100), 2),
        ];
    }

    public function overtimePay(User $user, float $hours, float $basic): float
    {
        $multiplier = PayrollSetting::getFloat('ot_hourly_multiplier', 1.5);
        $hourly = $basic / PayrollSetting::getFloat('working_days_per_month', 26) / 8;

        return round($hours * $hourly * $multiplier, 2);
    }

    public function computeForEmployee(User $emp, int $year, int $month, ?float $incrementPct = null): array
    {
        $latestApplied = Increment::where('user_id', $emp->id)
            ->where('status', 'Applied')
            ->orderByDesc('effective_date')
            ->orderByDesc('id')
            ->first();

        $yearsOfService = $emp->join_date ? $emp->join_date->floatDiffInYears(today()) : 0;
        $defaultPct = $latestApplied?->increment_pct ?? PayrollSetting::getFloat('default_increment_pct', 10);
        $incrementPct = $incrementPct ?? $defaultPct;

        $baseSalary = $latestApplied ? (float) $latestApplied->current_salary : (float) $emp->salary;
        // Only apply increment bump if years >= 1 OR using already-applied rate without changing salary again
        $salaryForPayroll = $yearsOfService >= 1
            ? round($baseSalary * (1 + $incrementPct / 100), 2)
            : (float) $emp->salary;

        $components = $this->salaryComponents($salaryForPayroll);
        $basic = $components['basic'];
        $hra = $components['hra'];
        $da = $components['da'];
        $allowances = $components['allowances'];

        $this->attendance->applyLateToAbsenceRule($emp, $year, $month);
        $otHours = $this->attendance->overtimeHours($emp, $year, $month);
        $overtimePay = $this->overtimePay($emp, $otHours, $basic);

        $nightDiff = $this->nightDifferential($emp, $year, $month, $basic);
        $gross = $basic + $hra + $da + $allowances + $overtimePay + $nightDiff;
        $pfEmp = $this->pf->employeeContribution($basic);
        $pfEr = $this->pf->employerContribution($basic);
        $tds = $this->tax->monthlyTdsForUser($emp, $gross - $overtimePay - $nightDiff);
        $rebate = $this->monthlyInvestmentRebate($emp, $year);
        $tds = max(0, round($tds - $rebate, 2));

        $workingDays = PayrollSetting::getFloat('working_days_per_month', 26);
        $dailyRate = $salaryForPayroll / max(1, $workingDays);
        $lateAbsences = $this->attendance->absenceDays($emp, $year, $month);
        $attendanceDeduction = round($lateAbsences * $dailyRate, 2);
        $unpaidLeave = $this->attendance->unpaidLeaveDays($emp, $year, $month);
        $unpaidLeaveDeduction = round($unpaidLeave * $dailyRate, 2);

        [$loanDeduction, $deferred] = $this->loans->protectedDeduction($emp, $gross, $pfEmp, $tds);

        $other = $attendanceDeduction + $unpaidLeaveDeduction;
        $net = max(0, $gross - $pfEmp - $tds - $loanDeduction - $other);

        $monthLabel = Carbon::create($year, $month, 1)->format('M Y');

        return [
            'month' => $monthLabel,
            'year' => $year,
            'month_num' => $month,
            'basic' => $basic,
            'hra' => $hra,
            'da' => $da,
            'allowances' => $allowances,
            'overtime_pay' => $overtimePay,
            'night_differential' => $nightDiff,
            'gross' => $gross,
            'tds' => $tds,
            'investment_rebate' => $rebate,
            'pf_employee' => $pfEmp,
            'pf_employer' => $pfEr,
            'loan_deduction' => $loanDeduction,
            'loan_deferred' => $deferred,
            'attendance_deduction' => $attendanceDeduction,
            'unpaid_leave_deduction' => $unpaidLeaveDeduction,
            'other_deductions' => $other,
            'net' => $net,
            'increment_pct' => $incrementPct,
            'base_salary' => $baseSalary,
            'salary_for_payroll' => $salaryForPayroll,
            'years_of_service' => $yearsOfService,
            'ot_hours' => $otHours,
            'status' => 'Generated',
        ];
    }

    public function nightDifferential(User $user, int $year, int $month, float $basic): float
    {
        $start = Carbon::create($year, $month, 1)->startOfMonth();
        $end = $start->copy()->endOfMonth();
        $assignments = \App\Models\ShiftAssignment::with('shift')
            ->where('user_id', $user->id)
            ->whereDate('from_date', '<=', $end)
            ->where(function ($q) use ($start) {
                $q->whereNull('to_date')->orWhereDate('to_date', '>=', $start);
            })
            ->get();

        $pct = 0.0;
        foreach ($assignments as $a) {
            if ($a->shift && ($a->shift->is_night || $a->shift->is_overnight)) {
                $pct = max($pct, (float) ($a->shift->night_differential_pct ?: PayrollSetting::getFloat('night_differential_pct', 15)));
            }
        }

        if ($pct <= 0) {
            return 0.0;
        }

        return round($basic * ($pct / 100), 2);
    }

    public function monthlyInvestmentRebate(User $user, int $year): float
    {
        // NBR-style simplified: 15% of approved investment proofs / 12, capped
        $approved = (float) \App\Models\InvestmentProof::where('user_id', $user->id)
            ->where('fiscal_year', $year)
            ->where('status', 'Approved')
            ->sum('amount');

        if ($approved <= 0) {
            return 0.0;
        }

        $rate = PayrollSetting::getFloat('investment_rebate_pct', 15);
        $maxAnnual = PayrollSetting::getFloat('investment_rebate_max', 1000000);
        $rebateAnnual = min($approved, $maxAnnual) * ($rate / 100);

        return round($rebateAnnual / 12, 2);
    }

    public function processMonth(int $year, int $month, array $incrementPctMap = [], ?int $preparedBy = null): \App\Models\PayrollRun
    {
        $run = \App\Models\PayrollRun::updateOrCreate(
            ['year' => $year, 'month' => $month],
            [
                'status' => 'Pending Approval',
                'prepared_by' => $preparedBy,
                'prepared_at' => now(),
                'approved_by' => null,
                'approved_at' => null,
            ]
        );

        $employees = User::where('role', '!=', 'admin')->where('status', 'Active')->get();
        $count = 0;
        $totalNet = 0.0;

        foreach ($employees as $emp) {
            $pct = isset($incrementPctMap[$emp->id]) ? (float) $incrementPctMap[$emp->id] : null;
            $data = $this->computeForEmployee($emp, $year, $month, $pct);

            Payslip::updateOrCreate(
                ['user_id' => $emp->id, 'year' => $year, 'month_num' => $month],
                [
                    'payroll_run_id' => $run->id,
                    'month' => $data['month'],
                    'basic' => $data['basic'],
                    'hra' => $data['hra'],
                    'da' => $data['da'],
                    'allowances' => $data['allowances'],
                    'overtime_pay' => $data['overtime_pay'],
                    'night_differential' => $data['night_differential'],
                    'gross' => $data['gross'],
                    'tds' => $data['tds'],
                    'investment_rebate' => $data['investment_rebate'],
                    'pf_employee' => $data['pf_employee'],
                    'pf_employer' => $data['pf_employer'],
                    'loan_deduction' => $data['loan_deduction'],
                    'attendance_deduction' => $data['attendance_deduction'],
                    'unpaid_leave_deduction' => $data['unpaid_leave_deduction'],
                    'other_deductions' => $data['other_deductions'],
                    'net' => $data['net'],
                    'status' => 'Pending Approval',
                ]
            );

            $this->loans->applyDeduction($emp, $data['loan_deduction']);

            if ($data['years_of_service'] >= 1) {
                $latestApplied = Increment::where('user_id', $emp->id)
                    ->where('status', 'Applied')
                    ->orderByDesc('effective_date')
                    ->orderByDesc('id')
                    ->first();

                if (! $latestApplied || (float) $latestApplied->increment_pct != (float) $data['increment_pct']) {
                    Increment::create([
                        'code' => 'IN'.str_pad((string) (Increment::max('id') + 1), 3, '0', STR_PAD_LEFT),
                        'user_id' => $emp->id,
                        'current_salary' => $data['base_salary'],
                        'increment_pct' => $data['increment_pct'],
                        'new_salary' => round($data['base_salary'] * (1 + $data['increment_pct'] / 100), 2),
                        'effective_date' => today(),
                        'reason' => 'Payroll increment rate applied',
                        'status' => 'Applied',
                    ]);
                }
            }

            $totalNet += $data['net'];
            $count++;
        }

        $run->update([
            'employee_count' => $count,
            'total_net' => $totalNet,
            'status' => 'Pending Approval',
        ]);

        return $run;
    }

    public function approveRun(\App\Models\PayrollRun $run, int $approverId): void
    {
        $run->update([
            'status' => 'Approved',
            'approved_by' => $approverId,
            'approved_at' => now(),
        ]);
        Payslip::where('payroll_run_id', $run->id)->update(['status' => 'Generated']);
    }

    public function calculateFestivalBonus(User $employee): array
    {
        $years = $employee->join_date ? $employee->join_date->floatDiffInYears(today()) : 0;
        $basicPct = PayrollSetting::getFloat('salary_basic_pct', 60);
        $basic = round((float) $employee->salary * ($basicPct / 100), 2);
        $fullRate = PayrollSetting::getFloat('festival_bonus_full_pct', 50);
        $prorataRate = PayrollSetting::getFloat('festival_bonus_prorata_pct', 25);
        $minYears = PayrollSetting::getFloat('festival_bonus_full_years', 1);
        $rate = $years >= $minYears ? $fullRate : $prorataRate;

        return [
            'basic' => $basic,
            'years_of_service' => round($years, 2),
            'festival_bonus' => round($basic * ($rate / 100), 2),
            'rate_pct' => $rate,
        ];
    }

    public function generateFestivalBonuses(): int
    {
        $count = 0;
        User::where('role', '!=', 'admin')->where('status', 'Active')->get()->each(function ($employee) use (&$count) {
            $calc = $this->calculateFestivalBonus($employee);
            Bonus::create([
                'code' => 'FB'.str_pad((string) (Bonus::max('id') + 1), 3, '0', STR_PAD_LEFT),
                'user_id' => $employee->id,
                'basic' => $calc['basic'],
                'years_of_service' => $calc['years_of_service'],
                'festival_bonus' => $calc['festival_bonus'],
                'performance_bonus' => 0,
                'status' => 'Pending',
            ]);
            $count++;
        });

        return $count;
    }
}
