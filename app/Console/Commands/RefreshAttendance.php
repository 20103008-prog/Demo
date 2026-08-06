<?php

namespace App\Console\Commands;

use App\Models\AttendanceRecord;
use App\Models\LeaveRequest;
use App\Models\User;
use App\Support\HolidayCalendar;
use Carbon\Carbon;
use Illuminate\Console\Command;

class RefreshAttendance extends Command
{
    protected $signature = 'attendance:refresh {from?} {to?}';
    protected $description = 'Refresh attendance records while skipping weekends and government holidays';

    public function handle(): int
    {
        $from = Carbon::parse($this->argument('from') ?? now()->subDays(30)->toDateString());
        $to = Carbon::parse($this->argument('to') ?? now()->toDateString());

        $employees = User::where('role', 'employee')->where('status', 'Active')->get();
        for ($date = $from->copy(); $date->lte($to); $date->addDay()) {
            foreach ($employees as $employee) {
                if (HolidayCalendar::isNonWorkingDay($date)) {
                    AttendanceRecord::where('user_id', $employee->id)
                        ->whereDate('date', $date)
                        ->where('status', 'Absent')
                        ->delete();
                    continue;
                }

                $exists = AttendanceRecord::where('user_id', $employee->id)
                    ->whereDate('date', $date)
                    ->exists();

                if ($exists) {
                    continue;
                }

                $hasApprovedLeave = LeaveRequest::where('user_id', $employee->id)
                    ->where('status', 'Approved')
                    ->whereDate('from_date', '<=', $date)
                    ->whereDate('to_date', '>=', $date)
                    ->exists();

                if ($hasApprovedLeave) {
                    continue;
                }

                AttendanceRecord::create([
                    'user_id' => $employee->id,
                    'date' => $date->toDateString(),
                    'hours' => 0,
                    'status' => 'Absent',
                ]);
            }
        }

        $this->info('Attendance refresh complete for '.$from->toDateString().' to '.$to->toDateString().'.');

        return self::SUCCESS;
    }
}
