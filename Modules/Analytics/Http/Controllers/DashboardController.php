<?php

namespace Modules\Analytics\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\AttendanceRecord;
use App\Models\AuditLog;
use App\Models\Bonus;
use App\Models\HrQuery;
use App\Models\LeaveRequest;
use App\Models\Loan;
use App\Models\Payslip;
use App\Models\Product;
use App\Models\Settlement;
use App\Models\SiteInquiry;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DashboardController extends Controller
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

    public function audit(): View
    {
        $logs = AuditLog::orderByDesc('logged_at')->get();

        return view('admin.audit', compact('logs'));
    }
}
