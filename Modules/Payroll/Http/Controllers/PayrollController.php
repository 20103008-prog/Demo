<?php

namespace Modules\Payroll\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Bonus;
use App\Models\Increment;
use App\Models\Loan;
use App\Models\Payslip;
use App\Models\Settlement;
use App\Models\User;
use App\Services\PayrollService;
use App\Services\PfService;
use App\Services\SettlementService;
use App\Services\TaxService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class PayrollController extends Controller
{
    public function payroll(Request $request): View
    {
        $year = (int) $request->input('year', now()->year);
        $month = (int) $request->input('month', now()->month);
        $employees = User::where('role', '!=', 'admin')->where('status', 'Active')->get();
        $payslips = Payslip::with('user')->where('month_num', $month)->where('year', $year)->get();

        $applied = Increment::whereIn('user_id', $employees->pluck('id'))
            ->where('status', 'Applied')
            ->orderByDesc('effective_date')
            ->orderByDesc('id')
            ->get()
            ->unique('user_id')
            ->keyBy('user_id');

        $employeeIncrements = $employees->mapWithKeys(fn ($employee) => [
            $employee->id => $applied->get($employee->id)?->increment_pct ?? 10,
        ])->toArray();

        $periodLabel = Carbon::create($year, $month, 1)->format('M Y');

        return view('admin.payroll', compact('employees', 'payslips', 'employeeIncrements', 'year', 'month', 'periodLabel'));
    }

    public function processPayroll(Request $request, PayrollService $payroll)
    {
        $data = $request->validate([
            'year' => 'required|integer|min:2020|max:2100',
            'month' => 'required|integer|min:1|max:12',
            'increment_pct' => 'required|array',
            'increment_pct.*' => 'required|numeric|min:10|max:100',
        ]);

        $employees = User::where('role', '!=', 'admin')->where('status', 'Active')->get();
        foreach ($employees as $emp) {
            $yearsOfService = $emp->join_date ? $emp->join_date->floatDiffInYears(today()) : 0;
            $latestApplied = Increment::where('user_id', $emp->id)->where('status', 'Applied')
                ->orderByDesc('effective_date')->orderByDesc('id')->first();
            $defaultPct = $latestApplied?->increment_pct ?? 10;
            $requestedPct = $data['increment_pct'][$emp->id] ?? $defaultPct;
            if ($yearsOfService < 1 && (float) $requestedPct != (float) $defaultPct) {
                return back()->with('error', 'Cannot change increment for '.$emp->name.' before completing 1 year of service.');
            }
        }

        $count = $payroll->processMonth((int) $data['year'], (int) $data['month'], $data['increment_pct'], Auth::id());
        $label = Carbon::create((int) $data['year'], (int) $data['month'], 1)->format('M Y');

        AuditLog::create([
            'action' => 'Payroll Prepared',
            'module' => 'Payroll',
            'user_name' => Auth::user()->name,
            'role' => 'Admin',
            'details' => "{$label} payroll prepared for {$count->employee_count} employees — pending approval.",
            'severity' => 'info',
            'logged_at' => now(),
        ]);

        return redirect()->route('admin.payroll', ['year' => $data['year'], 'month' => $data['month']])
            ->with('success', "Payroll prepared for {$count->employee_count} employees ({$label}). Awaiting maker-checker approval.");
    }

    public function taxPf(TaxService $tax, PfService $pf): View
    {
        $employees = User::where('role', '!=', 'admin')->where('status', 'Active')->with('payslips')->get();
        $slabs = $tax->slabSummary('general');
        $pfRates = $pf->rates();
        $categories = TaxService::categoryOptions();
        $taxRows = $employees->map(function (User $e) use ($tax, $pf, $categories) {
            $basic = round((float) $e->salary * 0.6, 2);
            $b = $tax->breakdownForUser($e);

            return [
                'name' => $e->name,
                'category' => $categories[$b['category']] ?? $b['category'],
                'tin' => $e->tin,
                'annual' => $b['annual_income'],
                'employment_deduction' => $b['employment_deduction'],
                'taxable' => $b['assessable_income'],
                'tax_free' => $b['tax_free_limit'],
                'annual_tax' => $b['annual_tax'],
                'monthly_tds' => $b['monthly_tds'],
                'pf_employee' => $pf->employeeContribution($basic),
                'pf_employer' => $pf->employerContribution($basic),
            ];
        });

        return view('admin.taxpf', compact('employees', 'slabs', 'pfRates', 'taxRows', 'categories'));
    }

    public function loans(): View
    {
        $loans = Loan::with('user')->latest()->get();
        $employees = User::where('role', '!=', 'admin')->where('status', 'Active')->orderBy('name')->get();

        return view('admin.loans', compact('loans', 'employees'));
    }

    public function storeLoan(Request $request)
    {
        $data = $request->validate([
            'user_id' => 'required|exists:users,id',
            'type' => 'required|in:Personal,Housing,Car,Education',
            'amount' => 'required|numeric|min:1',
            'installments' => 'required|integer|min:1',
            'start_date' => 'required|date',
        ]);

        $emi = round($data['amount'] / $data['installments'], 2);

        if ($emi < 1) {
            return back()
                ->withErrors(['installments' => 'Installments are too many for the amount. EMI must be at least 1.'])
                ->withInput();
        }

        Loan::create([
            'code' => 'LN'.str_pad((string) (Loan::max('id') + 1), 3, '0', STR_PAD_LEFT),
            'user_id' => $data['user_id'],
            'type' => $data['type'],
            'amount' => $data['amount'],
            'installments' => $data['installments'],
            'emi' => $emi,
            'outstanding' => $data['amount'],
            'status' => 'Active',
            'start_date' => $data['start_date'],
        ]);

        return back()->with('success', 'Loan registered.');
    }

    public function bonus(): View
    {
        $bonuses = Bonus::with('user')->latest()->get();
        $increments = Increment::with('user')->latest()->get();
        $employees = User::where('role', '!=', 'admin')->where('status', 'Active')->orderBy('name')->get();

        return view('admin.bonus', compact('bonuses', 'increments', 'employees'));
    }

    public function storeFestivalBonus(Request $request, PayrollService $payroll)
    {
        $count = $payroll->generateFestivalBonuses();

        return back()->with('success', "Festival bonus calculated for {$count} active employees.");
    }

    public function updateBonusStatus(Request $request, Bonus $bonus)
    {
        $data = $request->validate(['status' => 'required|in:Pending,Approved,Paid']);
        $bonus->update(['status' => $data['status']]);

        return back()->with('success', 'Bonus status updated.');
    }

    public function storeIncrement(Request $request)
    {
        $data = $request->validate([
            'user_id' => 'required|exists:users,id',
            'increment_pct' => 'required|numeric|min:0|max:100',
            'effective_date' => 'required|date',
            'reason' => 'nullable|string|max:500',
        ]);

        $employee = User::where('id', $data['user_id'])->where('status', 'Active')->firstOrFail();
        $yearsOfService = $employee->join_date ? $employee->join_date->floatDiffInYears(today()) : 0;

        if ($yearsOfService < 1) {
            return back()->with('error', 'Increment can only be applied after 1 year of service.');
        }

        $currentSalary = $employee->salary;
        $newSalary = round($currentSalary * (1 + $data['increment_pct'] / 100), 2);

        Increment::create([
            'code' => 'IN'.str_pad((string) (Increment::max('id') + 1), 3, '0', STR_PAD_LEFT),
            'user_id' => $employee->id,
            'current_salary' => $currentSalary,
            'increment_pct' => $data['increment_pct'],
            'new_salary' => $newSalary,
            'effective_date' => $data['effective_date'],
            'reason' => $data['reason'] ?? null,
            'status' => 'Applied',
        ]);

        AuditLog::create([
            'action' => 'Increment Applied',
            'module' => 'Payroll',
            'user_name' => Auth::user()->name,
            'role' => 'Admin',
            'details' => "Applied {$data['increment_pct']}% increment to {$employee->name} ({$currentSalary} → {$newSalary}).",
            'severity' => 'info',
            'logged_at' => now(),
        ]);

        return back()->with('success', 'Increment applied for '.$employee->name.'.');
    }

    public function updateIncrementStatus(Request $request, Increment $increment)
    {
        $data = $request->validate(['status' => 'required|in:Draft,Approved,Applied']);
        $increment->update(['status' => $data['status']]);

        return back()->with('success', 'Increment status updated.');
    }

    public function settlement(): View
    {
        $settlements = Settlement::with('user')->latest()->get();

        return view('admin.settlement', compact('settlements'));
    }

    public function updateSettlement(Request $request, Settlement $settlement)
    {
        $data = $request->validate(['status' => 'required|in:Initiated,Approved,Paid']);
        $settlement->update(['status' => $data['status']]);

        return back()->with('success', 'Settlement updated.');
    }

    public function prepareSettlement(User $employee, SettlementService $settlements): View
    {
        if ($employee->role === 'admin') {
            return back()->with('error', 'Cannot settle an admin account.');
        }

        $calc = $settlements->calculate($employee);
        $lastPayslip = $calc['last_payslip'];
        $yearsOfService = $calc['years_of_service'];
        $lastIncrementPct = $calc['last_increment_pct'];
        $leaveEncashment = $calc['leave_encashment'];
        $outstandingLoan = $calc['outstanding_loan'];
        $finalSalary = $calc['final_salary'];
        $pfEmployee = $calc['pf_employee'];
        $tds = $calc['tds'];
        $gratuity = $calc['gratuity'];
        $netSettlement = $calc['net_settlement'];

        return view('admin.employee-settlement', compact(
            'employee', 'lastPayslip', 'yearsOfService', 'lastIncrementPct',
            'leaveEncashment', 'outstandingLoan', 'finalSalary', 'pfEmployee',
            'tds', 'gratuity', 'netSettlement'
        ));
    }

    public function finalizeSettlement(Request $request, User $employee, SettlementService $settlements)
    {
        if ($employee->role === 'admin') {
            return back()->with('error', 'Cannot settle an admin account.');
        }

        if ($employee->status !== 'Active') {
            return back()->with('error', 'Settlement already completed or employee is inactive.');
        }

        $settlements->finalize($employee);

        AuditLog::create([
            'action' => 'Employee Removed',
            'module' => 'Employees',
            'user_name' => Auth::user()->name,
            'role' => 'Admin',
            'details' => 'Final settlement created for '.$employee->name,
            'severity' => 'warning',
            'logged_at' => now(),
        ]);

        return redirect()->route('admin.settlement')->with('success', 'Final settlement created and employee marked inactive.');
    }
}
