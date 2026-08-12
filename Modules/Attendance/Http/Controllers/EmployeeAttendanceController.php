<?php

namespace Modules\Attendance\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\AppNotification;
use App\Models\AttendanceRecord;
use App\Models\User;
use App\Services\AttendanceService;
use App\Support\HolidayCalendar;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class EmployeeAttendanceController extends Controller
{
    public function attendance(AttendanceService $attendance): View
    {
        $user = Auth::user();
        $records = AttendanceRecord::where('user_id', $user->id)->orderByDesc('date')->get();
        $today = $records->first(fn ($r) => $r->date->isToday());
        $holidayLabel = HolidayCalendar::isNonWorkingDay(today())
            ? HolidayCalendar::label(today())
            : null;
        $attendancePct = $attendance->monthlyPercentage($user, (int) now()->year, (int) now()->month);
        $lateThreshold = $attendance->lateThresholdFor($user);
        $otHours = $attendance->overtimeHours($user, (int) now()->year, (int) now()->month);

        return view('employee.attendance', compact(
            'user', 'records', 'today', 'holidayLabel', 'attendancePct', 'lateThreshold', 'otHours'
        ));
    }

    public function punch(Request $request, AttendanceService $attendance)
    {
        $user = Auth::user();

        if (HolidayCalendar::isNonWorkingDay(today())) {
            return back()->with('error', 'Today is a non-working day ('.HolidayCalendar::label(today()).').');
        }

        $today = AttendanceRecord::firstOrNew([
            'user_id' => $user->id,
            'date' => today()->toDateString(),
        ]);

        $lateThreshold = $attendance->lateThresholdFor($user);

        if (! $today->check_in) {
            $today->check_in = now()->format('H:i');
            $today->status = now()->format('H:i') > $lateThreshold ? 'Late' : 'Present';
            $today->hours = 0;
            $today->save();

            return back()->with('success', 'Punched in at '.$today->check_in);
        }

        if (! $today->check_out) {
            $today->check_out = now()->format('H:i');
            $in = Carbon::createFromFormat('H:i', $today->check_in);
            $out = Carbon::createFromFormat('H:i', $today->check_out);
            $today->hours = round($in->diffInMinutes($out) / 60, 1);

            if ($today->hours < 6) {
                $today->status = 'Early Departure';
            } elseif ($today->check_in > $lateThreshold) {
                $today->status = 'Late';
            } else {
                $today->status = 'Present';
            }

            $today->save();

            if ($today->hours < 6) {
                $dailyRate = $user->salary / 30;
                $proRated = round($dailyRate * ($today->hours / 8), 2);
                $message = 'Early departure recorded: '.$today->hours.' hours worked, pro-rated daily pay ₹'.number_format($proRated, 2).'.';

                if ($user->role === 'employee') {
                    $manager = User::where('department', $user->department)->where('role', 'manager')->first();
                    if ($manager) {
                        AppNotification::create([
                            'user_id' => $manager->id,
                            'type' => 'attendance',
                            'title' => 'Team Early Departure',
                            'body' => $user->name.' punched out early with '.$today->hours.' hours. '.$message,
                        ]);
                    }
                } else {
                    foreach (User::where('role', 'admin')->get() as $hr) {
                        AppNotification::create([
                            'user_id' => $hr->id,
                            'type' => 'attendance',
                            'title' => 'Manager Early Departure',
                            'body' => $user->name.' punched out early with '.$today->hours.' hours. '.$message,
                        ]);
                    }
                }
            }

            return back()->with('success', 'Punched out at '.$today->check_out);
        }

        return back()->with('error', 'Already punched in and out today.');
    }
}
