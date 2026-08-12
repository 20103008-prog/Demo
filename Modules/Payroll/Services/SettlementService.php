<?php

namespace Modules\Payroll\Services;

use App\Models\Increment;
use App\Models\LeaveRequest;
use App\Models\Loan;
use App\Models\PayrollSetting;
use App\Models\Payslip;
use App\Models\Settlement;
use App\Models\User;

class SettlementService
{
    public function __construct(
        protected TaxService $tax,
        protected PfService $pf,
        protected PayrollService $payroll,
    ) {}

    public function calculate(User $employee): array
    {
        $lastPayslip = Payslip::where('user_id', $employee->id)
            ->orderByDesc('year')
            ->orderByDesc('month_num')
            ->first();

        $yearsOfService = $employee->join_date
            ? round($employee->join_date->floatDiffInYears(now()), 2)
            : 0;

        $latestAppliedIncrement = Increment::where('user_id', $employee->id)
            ->where('status', 'Applied')
            ->orderByDesc('effective_date')
            ->orderByDesc('id')
            ->first();

        $lastIncrementPct = (float) ($latestAppliedIncrement?->increment_pct ?? 0);
        $baseSalary = (float) $employee->salary;
        $finalSalary = $lastIncrementPct > 0
            ? round($baseSalary * (1 + $lastIncrementPct / 100), 2)
            : $baseSalary;

        $components = $this->payroll->salaryComponents($finalSalary);
        $basic = $components['basic'];
        $pfEmployee = $this->pf->employeeContribution($basic);
        $tds = $this->tax->monthlyTdsForUser($employee, $finalSalary);

        $leaveEntitlement = PayrollSetting::getInt('leave_casual_per_year', 12);
        $encashDays = PayrollSetting::getFloat('leave_encashment_divisor', 26);
        $leaveDays = LeaveRequest::where('user_id', $employee->id)
            ->where('status', 'Approved')
            ->whereYear('from_date', now()->year)
            ->sum('days');
        $leaveEncashment = round(max(0, $leaveEntitlement - $leaveDays) * ($baseSalary / $encashDays), 2);

        $minGratuityYears = PayrollSetting::getFloat('gratuity_min_years', 5);
        $gratuityDays = PayrollSetting::getFloat('gratuity_days_per_year', 15);
        $gratuity = 0.0;
        if ($yearsOfService >= $minGratuityYears) {
            // (Basic × 15 × completed years) / 26
            $gratuity = round(($basic * $gratuityDays * floor($yearsOfService)) / $encashDays, 2);
        }

        $outstandingLoan = (float) Loan::where('user_id', $employee->id)
            ->where('status', 'Active')
            ->sum('outstanding');

        $netSettlement = max(0, $finalSalary + $leaveEncashment + $gratuity - $pfEmployee - $tds - $outstandingLoan);

        return [
            'last_payslip' => $lastPayslip,
            'years_of_service' => $yearsOfService,
            'last_increment_pct' => $lastIncrementPct,
            'base_salary' => $baseSalary,
            'final_salary' => $finalSalary,
            'basic' => $basic,
            'pf_employee' => $pfEmployee,
            'tds' => $tds,
            'leave_encashment' => $leaveEncashment,
            'gratuity' => $gratuity,
            'outstanding_loan' => $outstandingLoan,
            'net_settlement' => $netSettlement,
        ];
    }

    public function finalize(User $employee): Settlement
    {
        $calc = $this->calculate($employee);

        $settlement = Settlement::create([
            'user_id' => $employee->id,
            'exit_date' => today(),
            'last_basic' => $calc['base_salary'],
            'years_of_service' => $calc['years_of_service'],
            'gratuity' => $calc['gratuity'],
            'leave_encashment' => $calc['leave_encashment'],
            'last_increment_pct' => $calc['last_increment_pct'],
            'pf_employee' => $calc['pf_employee'],
            'tds' => $calc['tds'],
            'final_month_salary' => $calc['final_salary'],
            'outstanding_loan' => $calc['outstanding_loan'],
            'net_settlement' => $calc['net_settlement'],
            'status' => 'Initiated',
        ]);

        Loan::where('user_id', $employee->id)->where('status', 'Active')->update([
            'outstanding' => 0,
            'status' => 'Closed',
        ]);

        $employee->update(['status' => 'Inactive']);

        return $settlement;
    }
}
