<?php

namespace App\Http\Controllers;

use App\Models\AppNotification;
use App\Models\AttendanceRecord;
use App\Models\Bonus;
use App\Models\HrQuery;
use App\Models\Increment;
use App\Models\LeaveBalance;
use App\Models\LeaveRequest;
use App\Models\Loan;
use App\Models\OvertimeRequest;
use App\Models\PayrollSetting;
use App\Models\Payslip;
use App\Models\Settlement;
use App\Models\User;
use App\Services\AiQueryService;
use App\Services\AttendanceService;
use App\Services\PfService;
use App\Services\TaxService;
use App\Support\HolidayCalendar;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class EmployeeController extends Controller
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
            'from_date' => 'required|date',
            'to_date' => 'required|date|after_or_equal:from_date',
            'reason' => 'required|string|max:1000',
            'is_half_day' => 'nullable|boolean',
            'half_day_session' => 'nullable|in:AM,PM',
        ]);

        $from = Carbon::parse($data['from_date']);
        $to = Carbon::parse($data['to_date']);
        $halfDay = $request->boolean('is_half_day');

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

    public function payroll(TaxService $tax, PfService $pf): View
    {
        $user = Auth::user();
        $payslips = Payslip::where('user_id', $user->id)->orderByDesc('year')->orderByDesc('month_num')->get();
        $latest = $payslips->first();
        $bonuses = Bonus::where('user_id', $user->id)->latest()->get();
        $increments = Increment::where('user_id', $user->id)->latest()->get();
        $loans = Loan::where('user_id', $user->id)->latest()->get();
        $settlement = Settlement::where('user_id', $user->id)->latest()->first();
        $pfRates = $pf->rates();
        $annualEst = $tax->annualIncome((float) $user->salary);
        $nbr = $tax->breakdownForUser($user);
        $monthlyTdsEst = $nbr['monthly_tds'];
        $categories = \App\Services\TaxService::categoryOptions();

        return view('employee.payroll', compact(
            'user', 'payslips', 'latest', 'bonuses', 'increments', 'loans',
            'settlement', 'pfRates', 'annualEst', 'monthlyTdsEst', 'nbr', 'categories'
        ));
    }

    public function queries(): View
    {
        $user = Auth::user();
        $queries = HrQuery::where('user_id', $user->id)->latest()->get();

        return view('employee.queries', compact('user', 'queries'));
    }

    public function storeQuery(Request $request, AiQueryService $ai)
    {
        $data = $request->validate([
            'category' => 'required|string',
            'subject' => 'required|string|max:255',
            'description' => 'required|string|max:2000',
            'priority' => 'required|in:High,Medium,Low',
        ]);

        $match = $ai->match($data['subject'].' '.$data['description']);

        HrQuery::create([
            'code' => 'Q'.str_pad((string) (HrQuery::max('id') + 1), 3, '0', STR_PAD_LEFT),
            'user_id' => Auth::id(),
            'category' => $data['category'],
            'subject' => $data['subject'],
            'description' => $data['description'],
            'priority' => $data['priority'],
            'status' => 'Pending',
            'submitted_on' => today(),
            'ai_draft' => $match['draft'],
            'ai_category' => $match['category'],
            'ai_confidence' => $match['confidence'],
            'needs_manual_review' => $match['needs_manual_review'],
        ]);

        $msg = $match['draft']
            ? 'Query submitted. AI draft reply generated (confidence '.round($match['confidence'] * 100).'%).'
            : 'Query submitted for manual HR review.';

        return back()->with('success', $msg);
    }
}
