<?php

namespace Modules\Analytics\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\AttendanceRecord;
use App\Models\Bonus;
use App\Models\Loan;
use App\Models\OvertimeRequest;
use App\Models\Payslip;
use App\Models\Settlement;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class ManagerReportController extends Controller
{

    protected function teamIds(): array
    {
        $dept = \Illuminate\Support\Facades\Auth::user()->department;

        return \App\Models\User::where('department', $dept)
            ->whereIn('role', ['employee', 'manager'])
            ->pluck('id')
            ->all();
    }
    public function reports(Request $request): View
    {
        $dept = Auth::user()->department;
        $teamIds = $this->teamIds();
        $year = (int) $request->input('year', now()->year);
        $month = (int) $request->input('month', now()->month);

        $payroll = Payslip::whereIn('user_id', $teamIds)->where('month_num', $month)->where('year', $year)->sum('net');
        $tdsTotal = Payslip::whereIn('user_id', $teamIds)->where('month_num', $month)->where('year', $year)->sum('tds');
        $pfTotal = Payslip::whereIn('user_id', $teamIds)->where('month_num', $month)->where('year', $year)->sum('pf_employee');
        $loanDeducted = Payslip::whereIn('user_id', $teamIds)->where('month_num', $month)->where('year', $year)->sum('loan_deduction');
        $loanOutstanding = Loan::whereIn('user_id', $teamIds)->where('status', 'Active')->sum('outstanding');
        $bonusTotal = Bonus::whereIn('user_id', $teamIds)->sum('festival_bonus');
        $settlementTotal = Settlement::whereIn('user_id', $teamIds)->sum('net_settlement');

        $start = Carbon::create($year, $month, 1)->startOfMonth();
        $end = $start->copy()->endOfMonth();
        $presentDays = AttendanceRecord::whereIn('user_id', $teamIds)
            ->whereBetween('date', [$start, $end])
            ->whereIn('status', ['Present', 'Late', 'Half-day', 'Early Departure', 'Late (Absence Rule)'])
            ->count();
        $totalDays = max(1, AttendanceRecord::whereIn('user_id', $teamIds)->whereBetween('date', [$start, $end])->count());
        $attendancePct = round(($presentDays / $totalDays) * 100);
        $lateCount = AttendanceRecord::whereIn('user_id', $teamIds)
            ->whereBetween('date', [$start, $end])
            ->whereIn('status', ['Late', 'Late (Absence Rule)'])
            ->count();
        $absentCount = AttendanceRecord::whereIn('user_id', $teamIds)
            ->whereBetween('date', [$start, $end])
            ->where('status', 'Absent')
            ->count();

        $otTrend = OvertimeRequest::whereIn('user_id', $teamIds)
            ->selectRaw("DATE_FORMAT(date, '%b') as label, MONTH(date) as m, SUM(hours) as hours")
            ->groupBy('label', 'm')
            ->orderBy('m')
            ->get();

        if ($otTrend->isEmpty()) {
            $otChart = [
                'type' => 'bar',
                'data' => [
                    'labels' => ['No OT yet'],
                    'datasets' => [['label' => 'OT Hours', 'data' => [0], 'backgroundColor' => '#3b82f6']],
                ],
                'options' => ['responsive' => true, 'maintainAspectRatio' => false],
            ];
        } else {
            $otChart = [
                'type' => 'bar',
                'data' => [
                    'labels' => $otTrend->pluck('label')->values(),
                    'datasets' => [[
                        'label' => 'OT Hours',
                        'data' => $otTrend->pluck('hours')->map(fn ($v) => (float) $v)->values(),
                        'backgroundColor' => '#3b82f6',
                    ]],
                ],
                'options' => ['responsive' => true, 'maintainAspectRatio' => false],
            ];
        }

        $periodLabel = Carbon::create($year, $month, 1)->format('M Y');

        return view('manager.reports', compact(
            'dept', 'payroll', 'attendancePct', 'otChart', 'year', 'month', 'periodLabel',
            'tdsTotal', 'pfTotal', 'loanDeducted', 'loanOutstanding', 'bonusTotal',
            'settlementTotal', 'lateCount', 'absentCount'
        ));
    }
}
