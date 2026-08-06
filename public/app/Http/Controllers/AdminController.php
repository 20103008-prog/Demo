<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\AttendanceRecord;
use App\Models\Bonus;
use App\Models\HrQuery;
use App\Models\Increment;
use App\Models\LeaveRequest;
use App\Models\Loan;
use App\Models\Payslip;
use App\Models\Product;
use App\Models\Settlement;
use App\Models\SiteInquiry;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class AdminController extends Controller
{
    public function dashboard(): View
    {
        $activeEmployeeIds = User::where('role', '!=', 'admin')
            ->where('status', 'Active')
            ->pluck('id');

        $today = today();
        $presentToday = AttendanceRecord::whereDate('date', $today)
            ->whereIn('status', ['Present', 'Late', 'Half-day'])
            ->whereIn('user_id', $activeEmployeeIds)
            ->count();
        $presentPct = $activeEmployeeIds->count() ? round(($presentToday / $activeEmployeeIds->count()) * 100) : 0;
        $lateToday = AttendanceRecord::whereDate('date', $today)
            ->where('status', 'Late')
            ->whereIn('user_id', $activeEmployeeIds)
            ->count();
        $punchesToday = AttendanceRecord::whereDate('date', $today)
            ->whereNotNull('check_in')
            ->whereIn('user_id', $activeEmployeeIds)
            ->count();
        $notPunchedToday = max(0, $activeEmployeeIds->count() - $punchesToday);
        $onLeaveToday = LeaveRequest::where('status', 'Approved')
            ->whereDate('from_date', '<=', $today)
            ->whereDate('to_date', '>=', $today)
            ->count();

        $stats = [
            'employees' => $activeEmployeeIds->count(),
            'presentToday' => $presentToday,
            'presentPct' => $presentPct,
            'lateToday' => $lateToday,
            'punchesToday' => $punchesToday,
            'notPunchedToday' => $notPunchedToday,
            'onLeaveToday' => $onLeaveToday,
            'pendingLeaves' => LeaveRequest::where('status', 'Pending')->count(),
            'openQueries' => HrQuery::where('status', 'Pending')->count(),
            'payroll' => Payslip::where('month_num', (int) now()->format('n'))->where('year', (int) now()->year)->sum('net')
                ?: Payslip::where('year', 2025)->where('month_num', 7)->sum('net'),
            'activeLoans' => Loan::where('status', 'Active')->count(),
        ];

        $payrollTrend = Payslip::query()
            ->select('month_num', DB::raw('MAX(month) as month'), DB::raw('SUM(net) as total'))
            ->where('year', 2025)
            ->groupBy('month_num')
            ->orderBy('month_num')
            ->get();

        $punchStart = today()->subDays(6);
        $punchTrend = AttendanceRecord::query()
            ->select(DB::raw('DATE(date) as day'), DB::raw('COUNT(*) as total'))
            ->whereIn('user_id', $activeEmployeeIds)
            ->whereDate('date', '>=', $punchStart)
            ->whereNotNull('check_in')
            ->groupBy('day')
            ->orderBy('day')
            ->get()
            ->keyBy('day');

        $punchLabels = [];
        $punchData = [];
        $punchColors = [];

        for ($date = $punchStart->copy(); $date->lte($today); $date->addDay()) {
            $dayKey = $date->toDateString();
            $isToday = $date->isToday();
            $punchLabels[] = $date->format('D');
            $punchData[] = $punchTrend->get($dayKey)->total ?? 0;
            $punchColors[] = $isToday
                ? 'rgba(37, 99, 235, 0.8)'
                : 'rgba(148, 163, 184, 0.5)';
        }

        $todayLabel = $today->format('D');

        $payrollChart = [
            'type' => 'line',
            'data' => [
                'labels' => $payrollTrend->pluck('month')->values(),
                'datasets' => [[
                    'label' => 'Payroll (₹)',
                    'data' => $payrollTrend->pluck('total')->map(fn ($v) => (float) $v)->values(),
                    'borderColor' => '#2563eb',
                    'tension' => 0.3,
                    'fill' => false,
                ]],
            ],
            'options' => ['responsive' => true, 'maintainAspectRatio' => false],
        ];

        $notPunchedEmployees = User::where('role', '!=', 'admin')
            ->where('status', 'Active')
            ->whereDoesntHave('attendanceRecords', function ($query) use ($today) {
                $query->whereDate('date', $today)->whereNotNull('check_in');
            })
            ->orderBy('employee_code')
            ->get();

        $recentPunches = AttendanceRecord::with('user')
            ->whereDate('date', $today)
            ->whereNotNull('check_in')
            ->orderByDesc('check_out')
            ->orderByDesc('check_in')
            ->limit(8)
            ->get();

        $punchVolumeChart = [
            'type' => 'bar',
            'data' => [
                'labels' => $punchLabels,
                'datasets' => [[
                    'label' => 'Punch Volume',
                    'data' => $punchData,
                    'backgroundColor' => $punchColors,
                    'borderColor' => '#2563eb',
                    'borderWidth' => 1,
                ]],
            ],
            'options' => [
                'responsive' => true,
                'maintainAspectRatio' => false,
                'scales' => [
                    'x' => [
                        'ticks' => [
                            'precision' => 0,
                            'todayLabel' => $todayLabel,
                        ],
                    ],
                    'y' => [
                        'beginAtZero' => true,
                        'ticks' => ['precision' => 0],
                    ],
                ],
            ],
        ];

        return view('admin.dashboard', compact('stats', 'payrollChart', 'punchVolumeChart', 'notPunchedEmployees', 'recentPunches'));
    }

    public function todaySummary(): View
    {
        $today = today();
        $activeEmployeeIds = User::where('role', '!=', 'admin')
            ->where('status', 'Active')
            ->pluck('id');

        $presentToday = AttendanceRecord::whereDate('date', $today)
            ->whereIn('status', ['Present', 'Late', 'Half-day'])
            ->whereIn('user_id', $activeEmployeeIds)
            ->count();

        $lateToday = AttendanceRecord::whereDate('date', $today)
            ->where('status', 'Late')
            ->whereIn('user_id', $activeEmployeeIds)
            ->count();

        $punchesToday = AttendanceRecord::whereDate('date', $today)
            ->whereNotNull('check_in')
            ->whereIn('user_id', $activeEmployeeIds)
            ->count();

        $onLeaveToday = LeaveRequest::where('status', 'Approved')
            ->whereDate('from_date', '<=', $today)
            ->whereDate('to_date', '>=', $today)
            ->count();

        $notPunchedToday = max(0, $activeEmployeeIds->count() - $punchesToday);

        $notPunchedEmployees = User::where('role', '!=', 'admin')
            ->where('status', 'Active')
            ->whereDoesntHave('attendanceRecords', function ($query) use ($today) {
                $query->whereDate('date', $today)->whereNotNull('check_in');
            })
            ->orderBy('employee_code')
            ->get();

        return view('admin.today-summary', compact(
            'today',
            'presentToday',
            'lateToday',
            'punchesToday',
            'onLeaveToday',
            'notPunchedToday',
            'notPunchedEmployees'
        ));
    }

    public function notPunchedToday(): View
    {
        $today = today();
        $notPunchedEmployees = User::where('role', '!=', 'admin')
            ->where('status', 'Active')
            ->whereDoesntHave('attendanceRecords', function ($query) use ($today) {
                $query->whereDate('date', $today)->whereNotNull('check_in');
            })
            ->orderBy('employee_code')
            ->get();

        return view('admin.today-not-punched', compact('today', 'notPunchedEmployees'));
    }

    public function employeeHistory(User $employee): View
    {
        if ($employee->role === 'admin') {
            abort(404);
        }

        $today = today();
        $punchStart = $today->copy()->subDays(6);

        $attendanceRecords = AttendanceRecord::where('user_id', $employee->id)
            ->whereBetween('date', [$punchStart, $today])
            ->orderByDesc('date')
            ->get();

        $lateHistory = AttendanceRecord::where('user_id', $employee->id)
            ->where('status', 'Late')
            ->whereBetween('date', [$punchStart, $today])
            ->orderByDesc('date')
            ->get();

        $leaveHistory = LeaveRequest::where('user_id', $employee->id)
            ->where(function ($query) use ($punchStart, $today) {
                $query->whereBetween('from_date', [$punchStart, $today])
                    ->orWhereBetween('to_date', [$punchStart, $today])
                    ->orWhere(function ($sub) use ($punchStart, $today) {
                        $sub->where('from_date', '<', $punchStart)
                            ->where('to_date', '>', $today);
                    });
            })
            ->orderByDesc('from_date')
            ->get();

        return view('admin.employee-history', compact(
            'employee',
            'attendanceRecords',
            'lateHistory',
            'leaveHistory',
            'today',
            'punchStart'
        ));
    }

    public function todayPunches(): View
    {
        $today = today();
        $records = AttendanceRecord::with('user')
            ->whereDate('date', $today)
            ->whereNotNull('check_in')
            ->orderBy('check_in')
            ->get();

        return view('admin.today-punches', compact('records', 'today'));
    }

    public function lateToday(): View
    {
        $today = today();
        $records = AttendanceRecord::with('user')
            ->whereDate('date', $today)
            ->where('status', 'Late')
            ->orderBy('check_in')
            ->get();

        return view('admin.late-today', compact('records', 'today'));
    }

    public function employees(): View
    {
        $employees = User::where('role', '!=', 'admin')->orderBy('employee_code')->get();
        $departments = User::whereNotNull('department')->distinct()->pluck('department');
        $designations = User::whereNotNull('job_title')->distinct()->pluck('job_title');

        $employees->each(function ($employee) {
            $recentAttendances = $employee->attendanceRecords()
                ->orderByDesc('date')
                ->limit(3)
                ->get(['check_in', 'date']);

            $employee->late_flagged = $recentAttendances->count() === 3 && $recentAttendances->every(function ($attendance) {
                return $attendance->check_in && $attendance->check_in > '09:15';
            });
        });

        return view('admin.employees', compact('employees', 'departments', 'designations'));
    }

    public function createEmployee(): View
    {
        $departments = User::whereNotNull('department')->distinct()->pluck('department');
        $designations = User::whereNotNull('job_title')->distinct()->pluck('job_title');

        return view('admin.employee-create', compact('departments', 'designations'));
    }

    public function storeEmployee(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|unique:users,email',
            'phone' => 'nullable|string|max:30',
            'department' => 'nullable|string',
            'employee_code' => 'nullable|string|unique:users,employee_code',
            'job_title' => 'nullable|string',
            'role' => 'required|in:employee,manager',
            'salary' => 'required|numeric|min:0',
            'join_date' => 'nullable|date',
            'status' => 'required|in:Active,Inactive',
            'address' => 'nullable|string|max:1000',
            'weekly_off' => 'nullable|array',
            'weekly_off.*' => 'string',
            'portal_login' => 'nullable|boolean',
        ]);

        $next = User::whereNotNull('employee_code')->count() + 1;
        $code = $data['employee_code'] ?? 'EMP'.str_pad((string) $next, 3, '0', STR_PAD_LEFT);

        $data['employee_code'] = $code;
        $data['portal_login'] = $request->boolean('portal_login');
        $data['weekly_off'] = $request->input('weekly_off', []);

        User::create([
            ...$data,
            'password' => Hash::make('demo1234'),
        ]);

        AuditLog::create([
            'action' => 'Employee Added',
            'module' => 'Employees',
            'user_name' => Auth::user()->name,
            'role' => 'Admin',
            'details' => 'Added employee '.$data['name'],
            'severity' => 'info',
            'logged_at' => now(),
        ]);

        return back()->with('success', 'Employee created (password: demo1234).');
    }

    public function updateEmployee(Request $request, User $employee)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,'.$employee->id,
            'department' => 'required|string',
            'job_title' => 'required|string',
            'role' => 'required|in:employee,manager',
            'join_date' => 'required|date',
            'status' => 'required|in:Active,Inactive',
        ]);

        $employee->update($data);

        return back()->with('success', 'Employee updated.');
    }

    public function editEmployee(User $employee): View
    {
        $lastPayslip = Payslip::where('user_id', $employee->id)
            ->orderByDesc('year')
            ->orderByDesc('month_num')
            ->first();

        return view('admin.employee-edit', compact('employee', 'lastPayslip'));
    }

    public function prepareSettlement(User $employee): View
    {
        if ($employee->role === 'admin') {
            return back()->with('error', 'Cannot settle an admin account.');
        }

        $lastPayslip = Payslip::where('user_id', $employee->id)->orderByDesc('year')->orderByDesc('month_num')->first();
        $yearsOfService = $employee->join_date ? round($employee->join_date->floatDiffInYears(now()), 2) : 0;
        $latestAppliedIncrement = Increment::where('user_id', $employee->id)
            ->where('status', 'Applied')
            ->orderByDesc('effective_date')
            ->orderByDesc('id')
            ->first();

        $lastIncrementPct = $latestAppliedIncrement?->increment_pct ?? 0;
        $baseSalary = $employee->salary;
        $finalSalary = round($baseSalary * (1 + $lastIncrementPct / 100), 2);
        $basic = round($finalSalary * 0.6, 2);
        $pfEmployee = round($basic * 0.12, 2);
        $tds = round($finalSalary * 0.08, 2);
        $leaveDays = LeaveRequest::where('user_id', $employee->id)
            ->where('status', 'Approved')
            ->whereYear('from_date', now()->year)
            ->sum('days');
        $leaveEncashment = round(max(0, 12 - $leaveDays) * ($employee->salary / 26), 2);
        $outstandingLoan = Loan::where('user_id', $employee->id)->where('status', 'Active')->sum('outstanding');
        $netSettlement = max(0, $finalSalary + $leaveEncashment - $pfEmployee - $tds - $outstandingLoan);

        return view('admin.employee-settlement', compact(
            'employee', 'lastPayslip', 'yearsOfService', 'lastIncrementPct',
            'leaveEncashment', 'outstandingLoan', 'finalSalary', 'pfEmployee',
            'tds', 'netSettlement'
        ));
    }

    public function finalizeSettlement(Request $request, User $employee)
    {
        if ($employee->role === 'admin') {
            return back()->with('error', 'Cannot settle an admin account.');
        }

        if ($employee->status !== 'Active') {
            return back()->with('error', 'Settlement already completed or employee is inactive.');
        }

        $yearsOfService = $employee->join_date ? round($employee->join_date->floatDiffInYears(now()), 2) : 0;
        $latestAppliedIncrement = Increment::where('user_id', $employee->id)
            ->where('status', 'Applied')
            ->orderByDesc('effective_date')
            ->orderByDesc('id')
            ->first();

        $lastIncrementPct = $latestAppliedIncrement?->increment_pct ?? 0;
        $baseSalary = $latestAppliedIncrement ? $latestAppliedIncrement->current_salary : $employee->salary;
        $finalSalary = round($baseSalary * (1 + $lastIncrementPct / 100), 2);
        $basic = round($finalSalary * 0.6, 2);
        $pfEmployee = round($basic * 0.12, 2);
        $tds = round($finalSalary * 0.08, 2);
        $leaveDays = LeaveRequest::where('user_id', $employee->id)
            ->where('status', 'Approved')
            ->whereYear('from_date', now()->year)
            ->sum('days');
        $leaveEncashment = round(max(0, 12 - $leaveDays) * ($employee->salary / 26), 2);
        $outstandingLoan = Loan::where('user_id', $employee->id)->where('status', 'Active')->sum('outstanding');
        $netSettlement = max(0, $finalSalary + $leaveEncashment - $pfEmployee - $tds - $outstandingLoan);

        Settlement::create([
            'user_id' => $employee->id,
            'exit_date' => today(),
            'last_basic' => $baseSalary,
            'years_of_service' => $yearsOfService,
            'leave_encashment' => $leaveEncashment,
            'last_increment_pct' => $lastIncrementPct,
            'pf_employee' => $pfEmployee,
            'tds' => $tds,
            'final_month_salary' => $finalSalary,
            'outstanding_loan' => $outstandingLoan,
            'net_settlement' => $netSettlement,
            'status' => 'Initiated',
        ]);

        $employee->update(['status' => 'Inactive']);

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

    public function destroyEmployee(User $employee)
    {
        if ($employee->role === 'admin') {
            return back()->with('error', 'Cannot delete admin.');
        }

        if ($employee->status === 'Active') {
            return back()->with('error', 'Process final settlement before deleting an active employee.');
        }

        $employee->delete();

        return back()->with('success', 'Employee deleted.');
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

    public function payroll(): View
    {
        $employees = User::where('role', '!=', 'admin')->where('status', 'Active')->get();
        $payslips = Payslip::with('user')->where('month_num', 7)->where('year', 2025)->get();

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

        return view('admin.payroll', compact('employees', 'payslips', 'employeeIncrements'));
    }

    public function processPayroll(Request $request)
    {
        $data = $request->validate([
            'increment_pct' => 'required|array',
            'increment_pct.*' => 'required|numeric|min:10|max:100',
        ]);

        $employees = User::where('role', '!=', 'admin')->where('status', 'Active')->get();
        $appliedIncrements = Increment::whereIn('user_id', $employees->pluck('id'))
            ->where('status', 'Applied')
            ->orderByDesc('effective_date')
            ->orderByDesc('id')
            ->get()
            ->unique('user_id')
            ->keyBy('user_id');

        $count = 0;

        foreach ($employees as $emp) {
            $latestApplied = $appliedIncrements->get($emp->id);
            $yearsOfService = $emp->join_date ? $emp->join_date->floatDiffInYears(today()) : 0;
            $defaultPct = $latestApplied?->increment_pct ?? 10;
            $requestedPct = $data['increment_pct'][$emp->id] ?? $defaultPct;

            if ($yearsOfService < 1 && $requestedPct != $defaultPct) {
                return back()->with('error', 'Cannot change increment for '.$emp->name.' before completing 1 year of service.');
            }

            $incrementPct = $requestedPct;
            $baseSalary = $latestApplied ? $latestApplied->current_salary : $emp->salary;
            $salaryForPayroll = round($baseSalary * (1 + $incrementPct / 100), 2);
            $basic = round($salaryForPayroll * 0.6, 2);
            $hra = round($salaryForPayroll * 0.2, 2);
            $da = round($salaryForPayroll * 0.1, 2);
            $allowances = round($salaryForPayroll * 0.1, 2);
            $gross = $basic + $hra + $da + $allowances;
            $pfEmp = round($basic * 0.12, 2);
            $pfEr = round($basic * 0.12, 2);
            $tds = round($gross * 0.08, 2);
            $loan = (float) Loan::where('user_id', $emp->id)->where('status', 'Active')->sum('emi');
            $net = max(0, $gross - $pfEmp - $tds - $loan);

            Payslip::updateOrCreate(
                ['user_id' => $emp->id, 'year' => 2025, 'month_num' => 7],
                [
                    'month' => 'Jul 2025',
                    'basic' => $basic,
                    'hra' => $hra,
                    'da' => $da,
                    'allowances' => $allowances,
                    'overtime_pay' => 0,
                    'gross' => $gross,
                    'tds' => $tds,
                    'pf_employee' => $pfEmp,
                    'pf_employer' => $pfEr,
                    'loan_deduction' => $loan,
                    'other_deductions' => 0,
                    'net' => $net,
                    'status' => 'Generated',
                ]
            );

            if ($yearsOfService >= 1 && (! $latestApplied || $latestApplied->increment_pct != $incrementPct)) {
                Increment::create([
                    'code' => 'IN'.str_pad((string) (Increment::max('id') + 1), 3, '0', STR_PAD_LEFT),
                    'user_id' => $emp->id,
                    'current_salary' => $baseSalary,
                    'increment_pct' => $incrementPct,
                    'new_salary' => round($baseSalary * (1 + $incrementPct / 100), 2),
                    'effective_date' => today(),
                    'reason' => 'Payroll increment rate applied',
                    'status' => 'Applied',
                ]);
            }

            $count++;
        }

        AuditLog::create([
            'action' => 'Payroll Processed',
            'module' => 'Payroll',
            'user_name' => Auth::user()->name,
            'role' => 'Admin',
            'details' => "Jul 2025 payroll processed for {$count} employees.",
            'severity' => 'info',
            'logged_at' => now(),
        ]);

        return back()->with('success', "Payroll processed for {$count} employees.");
    }

    public function taxPf(): View
    {
        $employees = User::where('role', '!=', 'admin')->where('status', 'Active')->with('payslips')->get();

        return view('admin.taxpf', compact('employees'));
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

    public function storeFestivalBonus(Request $request)
    {
        User::where('role', '!=', 'admin')->where('status', 'Active')->get()->each(function ($employee) {
            $yearsOfService = $employee->join_date ? $employee->join_date->floatDiffInYears(today()) : 0;
            $basic = round($employee->salary * 0.6, 2);
            $rate = $yearsOfService >= 1 ? 0.5 : 0.25;

            Bonus::create([
                'code' => 'FB'.str_pad((string) (Bonus::max('id') + 1), 3, '0', STR_PAD_LEFT),
                'user_id' => $employee->id,
                'basic' => $basic,
                'years_of_service' => round($yearsOfService, 2),
                'festival_bonus' => round($basic * $rate, 2),
                'performance_bonus' => 0,
                'status' => 'Pending',
            ]);
        });

        return back()->with('success', 'Festival bonus calculated for all active employees.');
    }

    public function updateBonusStatus(Request $request, Bonus $bonus)
    {
        $data = $request->validate(['status' => 'required|in:Pending,Approved,Paid']);
        $bonus->update(['status' => $data['status']]);

        return back()->with('success', 'Bonus status updated.');
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

    public function queries(): View
    {
        $queries = HrQuery::with('user')->latest()->get();

        return view('admin.queries', compact('queries'));
    }

    public function replyQuery(Request $request, HrQuery $query)
    {
        $data = $request->validate(['response' => 'required|string|max:5000']);
        $query->update([
            'response' => $data['response'],
            'status' => 'Resolved',
        ]);

        return back()->with('success', 'Reply sent and query resolved.');
    }

    public function reports(): View
    {
        $payrollTotal = Payslip::where('year', 2025)->where('month_num', 7)->sum('net');
        $loanOutstanding = Loan::where('status', 'Active')->sum('outstanding');
        $byDept = User::where('role', '!=', 'admin')
            ->selectRaw('department, count(*) as total, sum(salary) as salary_cost')
            ->groupBy('department')
            ->get();

        $inquiries = SiteInquiry::with('product')->latest()->limit(20)->get();
        $products = Product::orderBy('sort_order')->get();

        return view('admin.reports', compact('payrollTotal', 'loanOutstanding', 'byDept', 'inquiries', 'products'));
    }

    public function products(): View
    {
        $products = Product::orderBy('sort_order')->get();

        return view('admin.products', compact('products'));
    }

    public function updateProduct(Request $request, Product $product)
    {
        $data = $request->validate([
            'price_monthly' => 'required|numeric|min:0',
            'price_yearly' => 'required|numeric|min:0',
            'is_published' => 'nullable|boolean',
            'is_featured' => 'nullable|boolean',
        ]);

        $product->update([
            'price_monthly' => $data['price_monthly'],
            'price_yearly' => $data['price_yearly'],
            'is_published' => $request->boolean('is_published'),
            'is_featured' => $request->boolean('is_featured'),
        ]);

        return back()->with('success', 'Product updated.');
    }

    public function inquiries(): View
    {
        $inquiries = SiteInquiry::with('product')->latest()->get();

        return view('admin.inquiries', compact('inquiries'));
    }

    public function updateInquiry(Request $request, SiteInquiry $inquiry)
    {
        $data = $request->validate(['status' => 'required|in:New,Contacted,Closed']);
        $inquiry->update(['status' => $data['status']]);

        return back()->with('success', 'Inquiry status updated.');
    }

    public function audit(): View
    {
        $logs = AuditLog::orderByDesc('logged_at')->get();

        return view('admin.audit', compact('logs'));
    }
}
