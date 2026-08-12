<?php

namespace Modules\Analytics\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\AppNotification;
use App\Models\AttendanceRecord;
use App\Models\HrQuery;
use App\Models\LeaveRequest;
use App\Models\Loan;
use App\Models\Payslip;
use App\Services\AttendanceService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class EmployeeDashboardController extends Controller
{
    public function dashboard(AttendanceService $attendance): View
    {
        $user = Auth::user();
        $today = AttendanceRecord::where('user_id', $user->id)->whereDate('date', today())->first();
        $pendingLeaves = LeaveRequest::where('user_id', $user->id)->where('status', 'Pending')->count();
        $latestPayslip = Payslip::where('user_id', $user->id)->latest('id')->first();
        $openQueries = HrQuery::where('user_id', $user->id)->where('status', 'Pending')->count();
        $recentAttendance = AttendanceRecord::where('user_id', $user->id)->orderByDesc('date')->limit(5)->get();
        $notifications = AppNotification::where('user_id', $user->id)->latest()->limit(5)->get();
        $attendancePct = $attendance->monthlyPercentage($user, (int) now()->year, (int) now()->month);
        $loanOutstanding = Loan::where('user_id', $user->id)->where('status', 'Active')->sum('outstanding');

        return view('employee.dashboard', compact(
            'user', 'today', 'pendingLeaves', 'latestPayslip', 'openQueries',
            'recentAttendance', 'notifications', 'attendancePct', 'loanOutstanding'
        ));
    }
}
