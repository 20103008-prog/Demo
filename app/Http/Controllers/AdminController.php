<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\AttendanceRecord;
use App\Models\Bonus;
use App\Models\Department;
use App\Models\Designation;
use App\Models\HrQuery;
use App\Models\Increment;
use App\Models\LeaveRequest;
use App\Models\Loan;
use App\Models\Payslip;
use App\Models\Product;
use App\Models\Settlement;
use App\Models\Shift;
use App\Models\ShiftAssignment;
use App\Models\SiteInquiry;
use App\Models\User;
use App\Services\PayrollService;
use App\Services\PfService;
use App\Services\SettlementService;
use App\Services\TaxService;
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

        $departments = Department::orderBy('name')->pluck('name');
        $designations = Designation::orderBy('name')->pluck('name');

        return view('admin.employees', compact('employees', 'departments', 'designations'));
    }

    public function createEmployee(): View
    {
        $departments = Department::orderBy('name')->pluck('name');
        $designations = Designation::orderBy('name')->pluck('name');

        return view('admin.employee-create', compact('departments', 'designations'));
    }

    public function departments(): View
    {
        $departments = Department::orderBy('name')->get();
        $designations = Designation::orderBy('name')->get();

        return view('admin.departments', compact('departments', 'designations'));
    }

    public function storeDepartment(Request $request)
    {
        $request->validate(['name' => 'required|string|max:255|unique:departments,name']);
        Department::create(['name' => trim($request->name)]);

        return back()->with('success', 'Department created successfully.');
    }

    public function updateDepartment(Request $request, Department $department)
    {
        $request->validate(['name' => 'required|string|max:255|unique:departments,name,'.$department->id]);
        $oldName = $department->name;
        $newName = trim($request->name);

        $department->update(['name' => $newName]);
        User::where('department', $oldName)->update(['department' => $newName]);

        return back()->with('success', 'Department updated successfully.');
    }

    public function destroyDepartment(Department $department)
    {
        $department->delete();

        return back()->with('success', 'Department deleted successfully.');
    }

    public function storeDesignation(Request $request)
    {
        $request->validate(['name' => 'required|string|max:255|unique:designations,name']);
        Designation::create(['name' => trim($request->name)]);

        return back()->with('success', 'Designation created successfully.');
    }

    public function updateDesignation(Request $request, Designation $designation)
    {
        $request->validate(['name' => 'required|string|max:255|unique:designations,name,'.$designation->id]);
        $oldName = $designation->name;
        $newName = trim($request->name);

        $designation->update(['name' => $newName]);
        User::where('job_title', $oldName)->update(['job_title' => $newName]);

        return back()->with('success', 'Designation updated successfully.');
    }

    public function destroyDesignation(Designation $designation)
    {
        $designation->delete();

        return back()->with('success', 'Designation deleted successfully.');
    }

    public function roster(): View
    {
        $shifts = Shift::withCount('assignments')->get();
        $employees = User::where('role', '!=', 'admin')->where('status', 'Active')->orderBy('name')->get();
        $assignments = ShiftAssignment::with(['user', 'shift'])->latest()->get();

        return view('admin.roster', compact('shifts', 'employees', 'assignments'));
    }

    public function storeShift(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'start_time' => 'required|string',
            'end_time' => 'required|string',
            'grace_minutes' => 'nullable|integer|min:0',
            'break_minutes' => 'nullable|integer|min:0',
            'ot_starts_after' => 'nullable|integer|min:0',
            'is_overnight' => 'nullable|boolean',
        ]);

        $data['grace_minutes'] = $data['grace_minutes'] ?? 0;
        $data['break_minutes'] = $data['break_minutes'] ?? 0;
        $data['ot_starts_after'] = $data['ot_starts_after'] ?? 0;
        $data['is_overnight'] = $request->boolean('is_overnight');

        Shift::create($data);

        return back()->with('success', 'Shift created successfully.');
    }

    public function updateShift(Request $request, Shift $shift)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'start_time' => 'required|string',
            'end_time' => 'required|string',
            'grace_minutes' => 'nullable|integer|min:0',
            'break_minutes' => 'nullable|integer|min:0',
            'ot_starts_after' => 'nullable|integer|min:0',
            'is_overnight' => 'nullable|boolean',
        ]);

        $data['grace_minutes'] = $data['grace_minutes'] ?? 0;
        $data['break_minutes'] = $data['break_minutes'] ?? 0;
        $data['ot_starts_after'] = $data['ot_starts_after'] ?? 0;
        $data['is_overnight'] = $request->boolean('is_overnight');

        $shift->update($data);

        return back()->with('success', 'Shift updated successfully.');
    }

    public function destroyShift(Shift $shift)
    {
        $shift->delete();

        return back()->with('success', 'Shift deleted successfully.');
    }

    public function storeShiftAssignment(Request $request)
    {
        $data = $request->validate([
            'user_id' => 'required|exists:users,id',
            'shift_id' => 'required|exists:shifts,id',
            'from_date' => 'required|date',
            'to_date' => 'nullable|date|after_or_equal:from_date',
        ]);

        ShiftAssignment::create($data);

        return back()->with('success', 'Shift assigned successfully.');
    }

    public function destroyShiftAssignment(ShiftAssignment $assignment)
    {
        $assignment->delete();

        return back()->with('success', 'Shift assignment removed successfully.');
    }

    public function storeEmployee(Request $request)
    {
        $portalLogin = $request->boolean('portal_login');

        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|unique:users,email',
            'phone' => 'nullable|string|max:30',
            'department' => 'nullable|string',
            'employee_code' => 'nullable|string|unique:users,employee_code',
            'job_title' => 'nullable|string',
            'role' => 'nullable|in:employee,manager',
            'salary' => 'nullable|numeric|min:0',
            'join_date' => 'nullable|date',
            'tax_category' => 'nullable|in:general,woman,senior,disabled,freedom_fighter',
            'tin' => 'nullable|string|max:20',
            'status' => 'required|in:Active,Inactive',
            'address' => 'nullable|string|max:1000',
            'weekly_off' => 'nullable|array',
            'weekly_off.*' => 'string',
            'portal_login' => 'nullable|boolean',
            'login_email' => 'nullable|email|unique:users,email',
            'password' => $portalLogin ? 'nullable|string|min:6|confirmed' : 'nullable',
        ]);

        $next = User::whereNotNull('employee_code')->count() + 1;
        $code = !empty($data['employee_code']) ? $data['employee_code'] : 'EMP'.str_pad((string) $next, 3, '0', STR_PAD_LEFT);

        $email = !empty($data['login_email']) ? $data['login_email'] : ($data['email'] ?? null);

        $data['employee_code'] = $code;
        $data['email'] = $email;
        $data['role'] = $data['role'] ?? 'employee';
        $data['salary'] = $data['salary'] ?? 0;
        $data['tax_category'] = $data['tax_category'] ?? 'general';
        $data['portal_login'] = $portalLogin;
        $data['weekly_off'] = $request->input('weekly_off', []);

        $passwordStr = !empty($request->input('password')) ? $request->input('password') : 'demo1234';

        unset($data['login_email'], $data['password_confirmation']);

        User::create([
            ...$data,
            'password' => Hash::make($passwordStr),
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

        return redirect()->route('admin.employees')->with('success', 'Employee created successfully (password: '.$passwordStr.').');
    }

    public function updateEmployee(Request $request, User $employee)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,'.$employee->id,
            'department' => 'required|string',
            'job_title' => 'required|string',
            'role' => 'required|in:employee,manager',
            'join_date' => 'nullable|date',
            'tax_category' => 'required|in:general,woman,senior,disabled,freedom_fighter',
            'tin' => 'nullable|string|max:20',
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

    public function reports(Request $request): View
    {
        $year = (int) $request->input('year', now()->year);
        $month = (int) $request->input('month', now()->month);

        $payrollTotal = Payslip::where('year', $year)->where('month_num', $month)->sum('net');
        $tdsTotal = Payslip::where('year', $year)->where('month_num', $month)->sum('tds');
        $pfTotal = Payslip::where('year', $year)->where('month_num', $month)->sum('pf_employee');
        $loanOutstanding = Loan::where('status', 'Active')->sum('outstanding');
        $loanDeducted = Payslip::where('year', $year)->where('month_num', $month)->sum('loan_deduction');
        $bonusTotal = Bonus::where('status', '!=', 'Pending')->sum('festival_bonus');
        $settlementTotal = Settlement::sum('net_settlement');
        $queryCount = HrQuery::count();
        $byDept = User::where('role', '!=', 'admin')
            ->selectRaw('department, count(*) as total, sum(salary) as salary_cost')
            ->groupBy('department')
            ->get();

        $inquiries = SiteInquiry::with('product')->latest()->limit(20)->get();
        $products = Product::orderBy('sort_order')->get();
        $periodLabel = Carbon::create($year, $month, 1)->format('M Y');

        return view('admin.reports', compact(
            'payrollTotal', 'loanOutstanding', 'byDept', 'inquiries', 'products',
            'tdsTotal', 'pfTotal', 'loanDeducted', 'bonusTotal', 'settlementTotal',
            'queryCount', 'year', 'month', 'periodLabel'
        ));
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
