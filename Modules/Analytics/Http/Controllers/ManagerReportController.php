<?php

namespace Modules\Analytics\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\AttendanceRecord;
use App\Models\Bonus;
use App\Models\LeaveRequest;
use App\Models\Loan;
use App\Models\OvertimeRequest;
use App\Models\Payslip;
use App\Models\Settlement;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ManagerReportController extends Controller
{
    protected function teamQuery()
    {
        $dept = Auth::user()->department;

        return User::query()
            ->where('department', $dept)
            ->whereIn('role', ['employee', 'manager'])
            ->where('status', 'Active');
    }

    protected function teamIds(): array
    {
        return $this->teamQuery()->pluck('id')->all();
    }

    protected function payload(Request $request): array
    {
        $dept = Auth::user()->department;
        $teamIds = $this->teamIds();
        $year = (int) $request->input('year', now()->year);
        $month = (int) $request->input('month', now()->month);
        $month = max(1, min(12, $month));

        $start = Carbon::create($year, $month, 1)->startOfMonth();
        $end = $start->copy()->endOfMonth();
        $periodLabel = $start->format('M Y');

        $payslips = Payslip::whereIn('user_id', $teamIds)
            ->where('month_num', $month)
            ->where('year', $year)
            ->get()
            ->keyBy('user_id');

        $payroll = (float) $payslips->sum('net');
        $tdsTotal = (float) $payslips->sum('tds');
        $pfTotal = (float) $payslips->sum('pf_employee');
        $loanDeducted = (float) $payslips->sum('loan_deduction');
        $loanOutstanding = (float) Loan::whereIn('user_id', $teamIds)->where('status', 'Active')->sum('outstanding');
        $bonusTotal = (float) Bonus::whereIn('user_id', $teamIds)->sum('festival_bonus');
        $settlementTotal = (float) Settlement::whereIn('user_id', $teamIds)->sum('net_settlement');

        $attendance = AttendanceRecord::whereIn('user_id', $teamIds)
            ->whereBetween('date', [$start, $end])
            ->get()
            ->groupBy('user_id');

        $presentStatuses = ['Present', 'Late', 'Half-day', 'Early Departure', 'Late (Absence Rule)'];
        $lateStatuses = ['Late', 'Late (Absence Rule)'];

        $presentDays = $attendance->flatten()->whereIn('status', $presentStatuses)->count();
        $totalDays = max(1, $attendance->flatten()->count());
        $attendancePct = round(($presentDays / $totalDays) * 100);
        $lateCount = $attendance->flatten()->whereIn('status', $lateStatuses)->count();
        $absentCount = $attendance->flatten()->where('status', 'Absent')->count();

        $leaves = LeaveRequest::whereIn('user_id', $teamIds)
            ->whereIn('status', ['Approved', 'Pending'])
            ->where(function ($q) use ($start, $end) {
                $q->whereBetween('from_date', [$start, $end])
                    ->orWhereBetween('to_date', [$start, $end])
                    ->orWhere(function ($inner) use ($start, $end) {
                        $inner->where('from_date', '<=', $start)->where('to_date', '>=', $end);
                    });
            })
            ->get()
            ->groupBy('user_id');

        $pendingLeaves = LeaveRequest::whereIn('user_id', $teamIds)->where('status', 'Pending')->count();
        $pendingOt = OvertimeRequest::whereIn('user_id', $teamIds)->where('status', 'Pending')->count();

        $team = $this->teamQuery()->orderBy('name')->get();

        $rows = $team->map(function (User $member) use ($attendance, $leaves, $payslips, $presentStatuses, $lateStatuses) {
            $records = $attendance->get($member->id, collect());
            $memberLeaves = $leaves->get($member->id, collect());

            return [
                'id' => $member->id,
                'name' => $member->name,
                'title' => $member->job_title,
                'present' => $records->whereIn('status', $presentStatuses)->count(),
                'late' => $records->whereIn('status', $lateStatuses)->count(),
                'absent' => $records->where('status', 'Absent')->count(),
                'leave_days' => (float) $memberLeaves->where('status', 'Approved')->sum('days'),
                'pending_leave' => $memberLeaves->where('status', 'Pending')->count(),
                'net' => (float) ($payslips->get($member->id)?->net ?? 0),
            ];
        });

        $otRows = OvertimeRequest::whereIn('user_id', $teamIds)
            ->where('date', '>=', now()->subMonths(5)->startOfMonth())
            ->get();

        $months = collect(range(0, 5))->map(fn ($i) => now()->copy()->startOfMonth()->subMonths(5 - $i));
        $otChart = [
            'type' => 'bar',
            'data' => [
                'labels' => $months->map(fn (Carbon $d) => $d->format('M'))->values(),
                'datasets' => [[
                    'label' => 'OT Hours',
                    'data' => $months->map(function (Carbon $d) use ($otRows) {
                        return (float) $otRows->filter(fn ($row) => $row->date && $row->date->isSameMonth($d))->sum('hours');
                    })->values(),
                    'backgroundColor' => '#1e3a8a',
                    'borderRadius' => 4,
                ]],
            ],
            'options' => [
                'responsive' => true,
                'maintainAspectRatio' => false,
                'plugins' => ['legend' => ['display' => false]],
                'scales' => [
                    'y' => ['beginAtZero' => true, 'ticks' => ['precision' => 0]],
                    'x' => ['grid' => ['display' => false]],
                ],
            ],
        ];

        return compact(
            'dept', 'payroll', 'attendancePct', 'otChart', 'year', 'month', 'periodLabel',
            'tdsTotal', 'pfTotal', 'loanDeducted', 'loanOutstanding', 'bonusTotal',
            'settlementTotal', 'lateCount', 'absentCount', 'rows', 'pendingLeaves', 'pendingOt', 'team'
        );
    }

    public function reports(Request $request): View
    {
        return view('manager.reports', $this->payload($request));
    }

    public function export(Request $request): StreamedResponse
    {
        $data = $this->payload($request);
        $filename = 'team-report-'.strtolower(str_replace(' ', '-', $data['periodLabel'])).'.csv';

        return response()->streamDownload(function () use ($data) {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['Department', $data['dept'], 'Period', $data['periodLabel']]);
            fputcsv($out, []);
            fputcsv($out, ['Employee', 'Title', 'Present', 'Late', 'Absent', 'Approved leave days', 'Pending leave', 'Net pay']);
            foreach ($data['rows'] as $row) {
                fputcsv($out, [
                    $row['name'],
                    $row['title'],
                    $row['present'],
                    $row['late'],
                    $row['absent'],
                    $row['leave_days'],
                    $row['pending_leave'],
                    $row['net'],
                ]);
            }
            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv']);
    }
}
