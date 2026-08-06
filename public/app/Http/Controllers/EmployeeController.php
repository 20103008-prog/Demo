<?php

namespace App\Http\Controllers;

use App\Models\AppNotification;
use App\Models\AttendanceRecord;
use App\Models\HrQuery;
use App\Models\LeaveRequest;
use App\Models\Payslip;
use App\Models\User;
use App\Support\HolidayCalendar;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class EmployeeController extends Controller
{
    public function dashboard(): View
    {
        $user = Auth::user();
        $today = AttendanceRecord::where('user_id', $user->id)->whereDate('date', today())->first();
        $pendingLeaves = LeaveRequest::where('user_id', $user->id)->where('status', 'Pending')->count();
        $latestPayslip = Payslip::where('user_id', $user->id)->latest('id')->first();
        $openQueries = HrQuery::where('user_id', $user->id)->where('status', 'Pending')->count();
        $recentAttendance = AttendanceRecord::where('user_id', $user->id)->orderByDesc('date')->limit(5)->get();
        $notifications = AppNotification::where('user_id', $user->id)->latest()->limit(5)->get();

        return view('employee.dashboard', compact(
            'user', 'today', 'pendingLeaves', 'latestPayslip', 'openQueries', 'recentAttendance', 'notifications'
        ));
    }

    public function attendance(): View
    {
        $user = Auth::user();
        $records = AttendanceRecord::where('user_id', $user->id)->orderByDesc('date')->get();
        $today = $records->first(fn ($r) => $r->date->isToday());
        $holidayLabel = HolidayCalendar::isNonWorkingDay(today())
            ? HolidayCalendar::label(today())
            : null;

        return view('employee.attendance', compact('user', 'records', 'today', 'holidayLabel'));
    }

    public function punch(Request $request)
    {
        $user = Auth::user();
        $today = AttendanceRecord::firstOrNew([
            'user_id' => $user->id,
            'date' => today()->toDateString(),
        ]);

        if (! $today->check_in) {
            $today->check_in = now()->format('H:i');
            $today->status = now()->format('H:i') > '09:15' ? 'Late' : 'Present';
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
            } elseif ($today->check_in > '09:15') {
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
                    $hrUsers = User::where('role', 'admin')->get();
                    foreach ($hrUsers as $hr) {
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

        return view('employee.leave', compact('user', 'leaves'));
    }

    public function storeLeave(Request $request)
    {
        $data = $request->validate([
            'type' => 'required|in:Casual,Sick,Earned,Compensatory',
            'from_date' => 'required|date',
            'to_date' => 'required|date|after_or_equal:from_date',
            'reason' => 'required|string|max:1000',
        ]);

        $from = Carbon::parse($data['from_date']);
        $to = Carbon::parse($data['to_date']);

        for ($date = $from->copy(); $date->lte($to); $date->addDay()) {
            if ($date->isFriday()) {
                return back()
                    ->withErrors(['from_date' => 'Leave requests cannot include Friday because it is already a non-working day.'])
                    ->withInput();
            }
        }

        $days = $from->diffInDays($to) + 1;

        LeaveRequest::create([
            'code' => 'LV'.str_pad((string) (LeaveRequest::max('id') + 1), 3, '0', STR_PAD_LEFT),
            'user_id' => Auth::id(),
            'type' => $data['type'],
            'from_date' => $data['from_date'],
            'to_date' => $data['to_date'],
            'days' => $days,
            'reason' => $data['reason'],
            'status' => 'Pending',
            'applied_on' => today(),
        ]);

        return back()->with('success', 'Leave request submitted.');
    }

    public function payroll(): View
    {
        $user = Auth::user();
        $payslips = Payslip::where('user_id', $user->id)->orderByDesc('year')->orderByDesc('month_num')->get();
        $latest = $payslips->first();

        return view('employee.payroll', compact('user', 'payslips', 'latest'));
    }

    public function queries(): View
    {
        $user = Auth::user();
        $queries = HrQuery::where('user_id', $user->id)->latest()->get();

        return view('employee.queries', compact('user', 'queries'));
    }

    public function storeQuery(Request $request)
    {
        $data = $request->validate([
            'category' => 'required|string',
            'subject' => 'required|string|max:255',
            'description' => 'required|string|max:2000',
            'priority' => 'required|in:High,Medium,Low',
        ]);

        HrQuery::create([
            'code' => 'Q'.str_pad((string) (HrQuery::max('id') + 1), 3, '0', STR_PAD_LEFT),
            'user_id' => Auth::id(),
            'category' => $data['category'],
            'subject' => $data['subject'],
            'description' => $data['description'],
            'priority' => $data['priority'],
            'status' => 'Pending',
            'submitted_on' => today(),
            'ai_draft' => $this->suggestAiDraft($data['subject'].' '.$data['description']),
        ]);

        return back()->with('success', 'Query submitted to HR.');
    }

    private function suggestAiDraft(string $text): ?string
    {
        $text = strtolower($text);
        $faqs = [
            ['keys' => ['tax', 'tds'], 'msg' => 'TDS is deducted monthly based on projected annual income. Salary revisions trigger recalculation.'],
            ['keys' => ['leave', 'balance'], 'msg' => 'You are entitled to 12 casual leaves per calendar year. Unused casual leaves lapse at year end.'],
            ['keys' => ['pf', 'provident'], 'msg' => 'PF contribution is 12% of basic for both employee and employer.'],
            ['keys' => ['payslip', 'salary'], 'msg' => 'Payslips are generated on the 5th of every month for the previous month.'],
            ['keys' => ['loan', 'emi'], 'msg' => 'Loan EMIs are deducted automatically. Maximum deduction is capped at 50% of net salary.'],
        ];

        foreach ($faqs as $faq) {
            foreach ($faq['keys'] as $key) {
                if (str_contains($text, $key)) {
                    return $faq['msg'];
                }
            }
        }

        return null;
    }
}
