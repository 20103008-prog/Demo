<?php

namespace Modules\Attendance\Services;

use App\Models\AttendanceRecord;
use App\Models\LeaveRequest;
use App\Models\PayrollSetting;
use App\Models\Shift;
use App\Models\ShiftAssignment;
use App\Models\User;
use App\Support\HolidayCalendar;
use Carbon\Carbon;

class AttendanceService
{
    public function lateThresholdFor(User $user, ?Carbon $date = null): string
    {
        $date = $date ?? today();
        $shift = $this->shiftFor($user, $date);
        if ($shift) {
            $start = Carbon::createFromFormat('H:i', substr($shift->start_time, 0, 5));
            $start->addMinutes((int) $shift->grace_minutes);

            return $start->format('H:i');
        }

        return PayrollSetting::getValue('late_threshold', '09:15');
    }

    public function shiftFor(User $user, Carbon $date): ?Shift
    {
        $assignment = ShiftAssignment::with('shift')
            ->where('user_id', $user->id)
            ->whereDate('from_date', '<=', $date->toDateString())
            ->where(function ($q) use ($date) {
                $q->whereNull('to_date')->orWhereDate('to_date', '>=', $date->toDateString());
            })
            ->latest('from_date')
            ->first();

        return $assignment?->shift;
    }

    public function monthlyPercentage(User $user, int $year, int $month): float
    {
        $start = Carbon::create($year, $month, 1)->startOfMonth();
        $end = $start->copy()->endOfMonth();
        if ($end->isFuture()) {
            $end = today();
        }

        $workingDays = 0;
        for ($d = $start->copy(); $d->lte($end); $d->addDay()) {
            if (! HolidayCalendar::isNonWorkingDay($d)) {
                $workingDays++;
            }
        }

        if ($workingDays === 0) {
            return 0.0;
        }

        $present = AttendanceRecord::where('user_id', $user->id)
            ->whereBetween('date', [$start->toDateString(), $end->toDateString()])
            ->whereIn('status', ['Present', 'Late', 'Half-day', 'Early Departure'])
            ->count();

        $approvedLeaveDays = LeaveRequest::where('user_id', $user->id)
            ->where('status', 'Approved')
            ->where(function ($q) use ($start, $end) {
                $q->whereBetween('from_date', [$start, $end])
                    ->orWhereBetween('to_date', [$start, $end]);
            })
            ->sum('days');

        $attended = min($workingDays, $present + (int) $approvedLeaveDays);

        return round(($attended / $workingDays) * 100, 1);
    }

    /**
     * Apply "3 late arrivals = 1 absence" for a user in a month.
     * Returns number of synthetic absence days applied.
     */
    public function applyLateToAbsenceRule(User $user, int $year, int $month): int
    {
        $threshold = PayrollSetting::getInt('lates_per_absence', 3);
        $start = Carbon::create($year, $month, 1)->startOfMonth();
        $end = $start->copy()->endOfMonth();

        $lates = AttendanceRecord::where('user_id', $user->id)
            ->whereBetween('date', [$start->toDateString(), $end->toDateString()])
            ->where('status', 'Late')
            ->orderBy('date')
            ->get();

        $absenceDays = intdiv($lates->count(), $threshold);
        if ($absenceDays <= 0) {
            return 0;
        }

        // Flag every Nth late as Late+Absence marker in notes via status change on synthetic day
        // Deduction is handled in payroll; mark last late of each group.
        for ($i = 0; $i < $absenceDays; $i++) {
            $record = $lates[($i + 1) * $threshold - 1] ?? null;
            if ($record && $record->status === 'Late') {
                $record->update(['status' => 'Late (Absence Rule)']);
            }
        }

        return $absenceDays;
    }

    public function unpaidLeaveDays(User $user, int $year, int $month): int
    {
        $start = Carbon::create($year, $month, 1)->startOfMonth();
        $end = $start->copy()->endOfMonth();

        return (int) LeaveRequest::where('user_id', $user->id)
            ->where('status', 'Approved')
            ->where('type', 'Unpaid')
            ->where(function ($q) use ($start, $end) {
                $q->whereBetween('from_date', [$start, $end])
                    ->orWhereBetween('to_date', [$start, $end]);
            })
            ->sum('days');
    }

    public function absenceDays(User $user, int $year, int $month): int
    {
        $start = Carbon::create($year, $month, 1)->startOfMonth();
        $end = $start->copy()->endOfMonth();

        $absents = AttendanceRecord::where('user_id', $user->id)
            ->whereBetween('date', [$start->toDateString(), $end->toDateString()])
            ->where('status', 'Absent')
            ->count();

        $lateAbsences = AttendanceRecord::where('user_id', $user->id)
            ->whereBetween('date', [$start->toDateString(), $end->toDateString()])
            ->where('status', 'Late (Absence Rule)')
            ->count();

        return $absents + $lateAbsences;
    }

    public function overtimeHours(User $user, int $year, int $month): float
    {
        return (float) \App\Models\OvertimeRequest::where('user_id', $user->id)
            ->where('status', 'Approved')
            ->whereYear('date', $year)
            ->whereMonth('date', $month)
            ->sum('hours');
    }
}
