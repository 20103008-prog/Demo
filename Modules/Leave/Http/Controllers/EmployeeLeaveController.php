<?php

namespace Modules\Leave\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\LeaveBalance;
use App\Models\LeaveRequest;
use App\Models\OvertimeRequest;
use App\Models\PayrollSetting;
use App\Support\HolidayCalendar;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class EmployeeLeaveController extends Controller
{
    public function leave(): View
    {
        $user = Auth::user();
        $leaves = LeaveRequest::where('user_id', $user->id)->latest()->get();
        $balance = LeaveBalance::firstOrCreate(
            ['user_id' => $user->id, 'year' => (int) now()->year],
            [
                'casual' => PayrollSetting::getInt('leave_casual_per_year', 12),
                'sick' => PayrollSetting::getInt('leave_sick_per_year', 6),
                'earned' => PayrollSetting::getInt('leave_earned_per_year', 15),
            ]
        );

        return view('employee.leave', compact('user', 'leaves', 'balance'));
    }

    public function storeLeave(Request $request)
    {
        $data = $request->validate([
            'type' => 'required|in:Casual,Sick,Earned,Compensatory,Unpaid',
            'from_date' => 'required|date|after_or_equal:today',
            'to_date' => 'required|date|after_or_equal:from_date',
            'reason' => 'required|string|max:1000',
            'is_half_day' => 'nullable|boolean',
            'half_day_session' => 'nullable|in:AM,PM',
        ], [
            'from_date.after_or_equal' => 'Leave can only be requested for today or a future date.',
        ]);

        $from = Carbon::parse($data['from_date'])->startOfDay();
        $to = Carbon::parse($data['to_date'])->startOfDay();
        $halfDay = $request->boolean('is_half_day');

        $overlap = LeaveRequest::where('user_id', Auth::id())
            ->whereIn('status', ['Pending', 'Approved'])
            ->whereDate('from_date', '<=', $to)
            ->whereDate('to_date', '>=', $from)
            ->exists();

        if ($overlap) {
            return back()->withErrors(['from_date' => 'You already have a pending or approved leave covering these dates.'])->withInput();
        }

        if ($halfDay && ! $from->equalTo($to)) {
            return back()->withErrors(['from_date' => 'Half-day leave must be a single date.'])->withInput();
        }

        $policyService = app(\App\Services\LeavePolicyService::class);
        $policy = $policyService->policyFor($data['type']);
        if ($halfDay && $policy && ! $policy->allow_half_day) {
            return back()->withErrors(['is_half_day' => 'Half-day not allowed for this leave type.'])->withInput();
        }

        for ($date = $from->copy(); $date->lte($to); $date->addDay()) {
            if (HolidayCalendar::isNonWorkingDay($date) && ! ($policy?->sandwich_rule)) {
                // non-working days skipped in day calc; block only if entire range is non-working without sandwich
            }
        }

        $days = $policyService->calculateDays($from, $to, $halfDay, $data['type']);
        if ($days <= 0) {
            return back()->withErrors(['from_date' => 'No working days in selected range.'])->withInput();
        }

        LeaveRequest::create([
            'code' => 'LV'.str_pad((string) (LeaveRequest::max('id') + 1), 3, '0', STR_PAD_LEFT),
            'user_id' => Auth::id(),
            'type' => $data['type'],
            'from_date' => $data['from_date'],
            'to_date' => $data['to_date'],
            'days' => $days,
            'is_half_day' => $halfDay,
            'half_day_session' => $halfDay ? ($data['half_day_session'] ?? 'AM') : null,
            'reason' => $data['reason'],
            'status' => 'Pending',
            'applied_on' => today(),
        ]);

        return back()->with('success', 'Leave request submitted.');
    }

    public function overtime(): View
    {
        $user = Auth::user();
        $items = OvertimeRequest::where('user_id', $user->id)->latest()->get();

        return view('employee.overtime', compact('user', 'items'));
    }

    public function storeOvertime(Request $request)
    {
        $data = $request->validate([
            'date' => 'required|date|before_or_equal:today',
            'hours' => 'required|numeric|min:0.5|max:12',
            'reason' => 'required|string|max:1000',
        ]);

        OvertimeRequest::create([
            'code' => 'OT'.str_pad((string) (OvertimeRequest::max('id') + 1), 3, '0', STR_PAD_LEFT),
            'user_id' => Auth::id(),
            'date' => $data['date'],
            'hours' => $data['hours'],
            'reason' => $data['reason'],
            'status' => 'Pending',
        ]);

        return back()->with('success', 'Overtime request submitted.');
    }
}
