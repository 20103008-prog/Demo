<?php

namespace App\Http\Controllers;

use App\Models\AttendanceRecord;
use App\Models\AuditLog;
use App\Models\LeaveRequest;
use App\Models\OvertimeRequest;
use App\Models\Payslip;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class ManagerController extends Controller
{
    public function dashboard(): View
    {
        $dept = Auth::user()->department;
        $team = User::where('department', $dept)->where('role', 'employee')->where('status', 'Active')->get();
        $pendingLeaves = LeaveRequest::whereHas('user', fn ($q) => $q->where('department', $dept))
            ->where('status', 'Pending')->count();
        $pendingOt = OvertimeRequest::whereHas('user', fn ($q) => $q->where('department', $dept))
            ->where('status', 'Pending')->count();
        $presentToday = AttendanceRecord::whereDate('date', today())
            ->whereIn('user_id', $team->pluck('id'))
            ->whereIn('status', ['Present', 'Late', 'Half-day'])
            ->count();
        $presentPct = $team->count() ? round(($presentToday / $team->count()) * 100) : 0;

        return view('manager.dashboard', compact('team', 'pendingLeaves', 'pendingOt', 'presentToday', 'presentPct'));
    }

    public function team(): View
    {
        $dept = Auth::user()->department;
        $team = User::where('department', $dept)->whereIn('role', ['employee', 'manager'])->orderBy('name')->get();

        return view('manager.team', compact('team', 'dept'));
    }

    public function leaves(): View
    {
        $dept = Auth::user()->department;
        $leaves = LeaveRequest::with('user')
            ->whereHas('user', fn ($q) => $q->where('department', $dept))
            ->latest()
            ->get();

        return view('manager.leaves', compact('leaves'));
    }

    public function reviewLeave(Request $request, LeaveRequest $leave)
    {
        $data = $request->validate([
            'status' => 'required|in:Approved,Rejected',
            'review_comment' => 'nullable|string|max:500',
        ]);

        $leave->update([
            'status' => $data['status'],
            'review_comment' => $data['review_comment'] ?? null,
            'reviewed_by' => Auth::id(),
        ]);

        AuditLog::create([
            'code' => 'AL'.str_pad((string) (AuditLog::max('id') + 1), 3, '0', STR_PAD_LEFT),
            'action' => 'Leave '.$data['status'],
            'module' => 'Leaves',
            'user_name' => Auth::user()->name,
            'role' => 'Manager',
            'details' => $data['status'].' leave '.$leave->code.' for '.$leave->user->name,
            'severity' => $data['status'] === 'Rejected' ? 'warning' : 'info',
            'logged_at' => now(),
        ]);

        return back()->with('success', 'Leave '.$data['status'].'.');
    }

    public function bulkApproveLeaves(Request $request)
    {
        $ids = $request->validate(['ids' => 'required|array'])['ids'];
        LeaveRequest::whereIn('id', $ids)->where('status', 'Pending')->update([
            'status' => 'Approved',
            'reviewed_by' => Auth::id(),
        ]);

        return back()->with('success', 'Selected leaves approved.');
    }

    public function overtime(): View
    {
        $dept = Auth::user()->department;
        $items = OvertimeRequest::with('user')
            ->whereHas('user', fn ($q) => $q->where('department', $dept))
            ->latest()
            ->get();

        return view('manager.overtime', compact('items'));
    }

    public function reviewOvertime(Request $request, OvertimeRequest $overtime)
    {
        $data = $request->validate(['status' => 'required|in:Approved,Rejected']);
        $overtime->update(['status' => $data['status']]);

        return back()->with('success', 'Overtime '.$data['status'].'.');
    }

    public function reports(): View
    {
        $dept = Auth::user()->department;
        $teamIds = User::where('department', $dept)->pluck('id');
        $payroll = Payslip::whereIn('user_id', $teamIds)->where('month_num', 7)->where('year', 2025)->sum('net');

        $presentDays = AttendanceRecord::whereIn('user_id', $teamIds)
            ->whereIn('status', ['Present', 'Late', 'Half-day'])
            ->count();
        $totalDays = max(1, AttendanceRecord::whereIn('user_id', $teamIds)->count());
        $attendancePct = round(($presentDays / $totalDays) * 100);

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

        return view('manager.reports', compact('dept', 'payroll', 'attendancePct', 'otChart'));
    }
}
