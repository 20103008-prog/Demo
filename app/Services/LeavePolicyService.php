<?php

namespace App\Services;

use App\Models\AttendanceRecord;
use App\Models\InvestmentProof;
use App\Models\LeavePolicy;
use App\Models\LeaveRequest;
use App\Models\PayrollSetting;
use App\Models\Shift;
use App\Models\ShiftAssignment;
use App\Models\User;
use App\Support\HolidayCalendar;
use Carbon\Carbon;

class LeavePolicyService
{
    public function policyFor(string $type): ?LeavePolicy
    {
        return LeavePolicy::where('type', $type)->where('is_active', true)->first();
    }

    public function calculateDays(Carbon $from, Carbon $to, bool $halfDay = false, string $type = 'Casual'): float
    {
        if ($halfDay) {
            return 0.5;
        }

        $policy = $this->policyFor($type);
        $sandwich = $policy?->sandwich_rule ?? false;
        $days = 0.0;

        for ($d = $from->copy(); $d->lte($to); $d->addDay()) {
            if (HolidayCalendar::isNonWorkingDay($d)) {
                if ($sandwich && $d->gt($from) && $d->lt($to)) {
                    $days += 1; // sandwich: non-working between leave days counts
                }
                continue;
            }
            $days += 1;
        }

        return max(0.5, $days);
    }

    public function applyCarryForward(User $user, int $toYear): void
    {
        $prev = $user->leaveBalances()->where('year', $toYear - 1)->first();
        if (! $prev) {
            return;
        }

        $balance = $user->leaveBalances()->firstOrCreate(
            ['user_id' => $user->id, 'year' => $toYear],
            [
                'casual' => PayrollSetting::getInt('leave_casual_per_year', 12),
                'sick' => PayrollSetting::getInt('leave_sick_per_year', 6),
                'earned' => PayrollSetting::getInt('leave_earned_per_year', 15),
            ]
        );

        foreach (['Casual' => 'casual', 'Sick' => 'sick', 'Earned' => 'earned'] as $type => $field) {
            $policy = $this->policyFor($type);
            if ($policy && $policy->carry_forward) {
                $carry = min((int) $prev->{$field}, (int) $policy->max_carry_forward);
                $balance->{$field} = (int) $balance->{$field} + $carry;
            }
        }
        $balance->save();
    }
}
