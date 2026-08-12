<?php

namespace Modules\Attendance\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\AttendanceRecord;
use App\Models\LeaveRequest;
use App\Models\OvertimeRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class ManagerAttendanceController extends Controller
{

    protected function teamIds(): array
    {
        $dept = \Illuminate\Support\Facades\Auth::user()->department;

        return \App\Models\User::where('department', $dept)
            ->whereIn('role', ['employee', 'manager'])
            ->pluck('id')
            ->all();
    }
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
            ->whereIn('status', ['Present', 'Late', 'Half-day', 'Early Departure'])
            ->count();
        $presentPct = $team->count() ? round(($presentToday / $team->count()) * 100) : 0;
        $lateToday = AttendanceRecord::whereDate('date', today())
            ->whereIn('user_id', $team->pluck('id'))
            ->whereIn('status', ['Late', 'Late (Absence Rule)'])
            ->count();
        $absentToday = AttendanceRecord::whereDate('date', today())
            ->whereIn('user_id', $team->pluck('id'))
            ->where('status', 'Absent')
            ->count();

        return view('manager.dashboard', compact(
            'team', 'pendingLeaves', 'pendingOt', 'presentToday', 'presentPct', 'lateToday', 'absentToday'
        ));
    }

    public function team(): View
    {
        $dept = Auth::user()->department;
        $team = User::where('department', $dept)->whereIn('role', ['employee', 'manager'])->orderBy('name')->get();

        return view('manager.team', compact('team', 'dept'));
    }

    public function attendance(Request $request): View
    {
        $dept = Auth::user()->department;
        $date = $request->input('date', today()->toDateString());
        $team = User::where('department', $dept)->where('role', 'employee')->where('status', 'Active')->orderBy('name')->get();
        $records = AttendanceRecord::with('user')
            ->whereIn('user_id', $team->pluck('id'))
            ->whereDate('date', $date)
            ->get()
            ->keyBy('user_id');

        $lateCount = $records->filter(fn ($r) => str_starts_with($r->status, 'Late'))->count();
        $absentCount = $records->where('status', 'Absent')->count();
        $presentCount = $records->filter(fn ($r) => in_array($r->status, ['Present', 'Late', 'Half-day', 'Early Departure', 'Late (Absence Rule)'], true))->count();

        return view('manager.attendance', compact(
            'dept', 'date', 'team', 'records', 'lateCount', 'absentCount', 'presentCount'
        ));
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
}
