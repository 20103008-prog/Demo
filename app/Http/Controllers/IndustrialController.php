<?php

namespace App\Http\Controllers;

use App\Models\AppNotification;
use App\Models\AuditLog;
use App\Models\BiometricDevice;
use App\Models\Branch;
use App\Models\Company;
use App\Models\EmployeeDocument;
use App\Models\Increment;
use App\Models\InvestmentProof;
use App\Models\LeavePolicy;
use App\Models\PayrollRun;
use App\Models\Payslip;
use App\Models\PerformanceReview;
use App\Models\Shift;
use App\Models\ShiftAssignment;
use App\Models\ShiftSwapRequest;
use App\Models\User;
use App\Services\AnalyticsService;
use App\Services\PayrollService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class IndustrialController extends Controller
{
    public function companies(): View
    {
        $companies = Company::with('branches')->orderBy('name')->get();

        return view('admin.companies', compact('companies'));
    }

    public function storeCompany(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:companies,code',
            'address' => 'nullable|string',
            'tin' => 'nullable|string|max:50',
            'phone' => 'nullable|string|max:30',
            'email' => 'nullable|email',
        ]);
        Company::create($data + ['is_active' => true]);

        return back()->with('success', 'Company created.');
    }

    public function storeBranch(Request $request)
    {
        $data = $request->validate([
            'company_id' => 'required|exists:companies,id',
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:branches,code',
            'address' => 'nullable|string',
        ]);
        Branch::create($data + ['is_active' => true]);

        return back()->with('success', 'Branch created.');
    }

    public function leavePolicies(): View
    {
        $policies = LeavePolicy::orderBy('type')->get();

        return view('admin.leave-policies', compact('policies'));
    }

    public function updateLeavePolicy(Request $request, LeavePolicy $policy)
    {
        $data = $request->validate([
            'annual_quota' => 'required|integer|min:0|max:60',
            'carry_forward' => 'nullable|boolean',
            'max_carry_forward' => 'required|integer|min:0|max:30',
            'allow_half_day' => 'nullable|boolean',
            'sandwich_rule' => 'nullable|boolean',
            'is_active' => 'nullable|boolean',
        ]);
        $policy->update([
            'annual_quota' => $data['annual_quota'],
            'max_carry_forward' => $data['max_carry_forward'],
            'carry_forward' => $request->boolean('carry_forward'),
            'allow_half_day' => $request->boolean('allow_half_day'),
            'sandwich_rule' => $request->boolean('sandwich_rule'),
            'is_active' => $request->boolean('is_active'),
        ]);

        return back()->with('success', 'Leave policy updated.');
    }

    public function documents(): View
    {
        $docs = EmployeeDocument::with('user')->latest()->limit(200)->get();
        $employees = User::where('role', '!=', 'admin')->orderBy('name')->get();

        return view('admin.documents', compact('docs', 'employees'));
    }

    public function storeDocument(Request $request)
    {
        $data = $request->validate([
            'user_id' => 'required|exists:users,id',
            'type' => 'required|in:nid,joining_letter,tin,contract,other',
            'title' => 'required|string|max:255',
            'file' => 'required|file|max:5120|mimes:pdf,jpg,jpeg,png,doc,docx',
        ]);
        $path = $request->file('file')->store('documents', 'public');
        EmployeeDocument::create([
            'user_id' => $data['user_id'],
            'type' => $data['type'],
            'title' => $data['title'],
            'file_path' => $path,
            'mime' => $request->file('file')->getMimeType(),
            'size' => $request->file('file')->getSize(),
            'uploaded_by' => Auth::id(),
        ]);

        return back()->with('success', 'Document uploaded.');
    }

    public function investments(): View
    {
        $proofs = InvestmentProof::with('user')->latest()->get();

        return view('admin.investments', compact('proofs'));
    }

    public function reviewInvestment(Request $request, InvestmentProof $proof)
    {
        $data = $request->validate(['status' => 'required|in:Approved,Rejected', 'notes' => 'nullable|string']);
        $proof->update([
            'status' => $data['status'],
            'notes' => $data['notes'] ?? null,
            'reviewed_by' => Auth::id(),
        ]);

        return back()->with('success', 'Investment proof '.$data['status'].'.');
    }

    public function appraisals(): View
    {
        $reviews = PerformanceReview::with(['user', 'reviewer'])->latest()->get();
        $employees = User::where('role', '!=', 'admin')->where('status', 'Active')->orderBy('name')->get();

        return view('admin.appraisals', compact('reviews', 'employees'));
    }

    public function storeAppraisal(Request $request)
    {
        $data = $request->validate([
            'user_id' => 'required|exists:users,id',
            'year' => 'required|integer|min:2020',
            'period' => 'required|in:Annual,Mid-year',
            'score' => 'required|numeric|min:0|max:100',
            'recommended_increment_pct' => 'required|numeric|min:0|max:100',
            'comments' => 'nullable|string',
        ]);

        PerformanceReview::create([
            'code' => 'PR'.str_pad((string) (PerformanceReview::max('id') + 1), 3, '0', STR_PAD_LEFT),
            'user_id' => $data['user_id'],
            'reviewer_id' => Auth::id(),
            'year' => $data['year'],
            'period' => $data['period'],
            'score' => $data['score'],
            'recommended_increment_pct' => $data['recommended_increment_pct'],
            'comments' => $data['comments'] ?? null,
            'status' => 'Submitted',
        ]);

        return back()->with('success', 'Appraisal saved.');
    }

    public function applyAppraisal(PerformanceReview $review)
    {
        if ($review->status === 'Applied') {
            return back()->with('error', 'Already applied.');
        }
        $emp = $review->user;
        $newSalary = round((float) $emp->salary * (1 + (float) $review->recommended_increment_pct / 100), 2);
        Increment::create([
            'code' => 'IN'.str_pad((string) (Increment::max('id') + 1), 3, '0', STR_PAD_LEFT),
            'user_id' => $emp->id,
            'current_salary' => $emp->salary,
            'increment_pct' => $review->recommended_increment_pct,
            'new_salary' => $newSalary,
            'effective_date' => today(),
            'reason' => 'Performance appraisal '.$review->code,
            'status' => 'Applied',
        ]);
        $emp->update(['salary' => $newSalary]);
        $review->update(['status' => 'Applied']);

        return back()->with('success', 'Increment applied from appraisal.');
    }

    public function payrollApprovals(): View
    {
        $runs = PayrollRun::with(['preparedBy', 'approvedBy'])->latest()->get();

        return view('admin.payroll-approvals', compact('runs'));
    }

    public function approvePayroll(Request $request, PayrollRun $run, PayrollService $payroll)
    {
        $data = $request->validate(['action' => 'required|in:Approved,Rejected']);
        if ($run->prepared_by === Auth::id() && $data['action'] === 'Approved') {
            return back()->with('error', 'Maker-checker: preparer cannot approve the same run.');
        }
        if ($data['action'] === 'Approved') {
            $payroll->approveRun($run, Auth::id());
        } else {
            $run->update(['status' => 'Rejected', 'approved_by' => Auth::id(), 'approved_at' => now()]);
            Payslip::where('payroll_run_id', $run->id)->update(['status' => 'Rejected']);
        }
        AuditLog::create([
            'action' => 'Payroll '.$data['action'],
            'module' => 'Payroll',
            'user_name' => Auth::user()->name,
            'role' => 'Admin',
            'details' => $run->label().' '.$data['action'],
            'severity' => 'info',
            'logged_at' => now(),
        ]);

        return back()->with('success', 'Payroll run '.$data['action'].'.');
    }

    public function bankAdvice(Request $request): StreamedResponse|View
    {
        $year = (int) $request->input('year', now()->year);
        $month = (int) $request->input('month', now()->month);
        $payslips = Payslip::with('user')->where('year', $year)->where('month_num', $month)
            ->whereIn('status', ['Generated', 'Pending Approval'])->get();

        if ($request->boolean('download')) {
            $filename = sprintf('bank-advice-%04d-%02d.csv', $year, $month);

            return response()->streamDownload(function () use ($payslips) {
                $out = fopen('php://output', 'w');
                fputcsv($out, ['Employee Code', 'Name', 'Bank', 'Account', 'Routing', 'Amount', 'Narration']);
                foreach ($payslips as $p) {
                    fputcsv($out, [
                        $p->user->employee_code,
                        $p->user->name,
                        $p->user->bank_name,
                        $p->user->bank_account,
                        $p->user->bank_routing,
                        $p->net,
                        'Salary '.$p->month,
                    ]);
                }
                fclose($out);
            }, $filename, ['Content-Type' => 'text/csv']);
        }

        return view('admin.bank-advice', compact('payslips', 'year', 'month'));
    }

    public function biometrics(): View
    {
        $devices = BiometricDevice::with('branch')->latest()->get();
        $branches = Branch::orderBy('name')->get();

        return view('admin.biometrics', compact('devices', 'branches'));
    }

    public function storeDevice(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string',
            'serial' => 'required|string|unique:biometric_devices,serial',
            'ip' => 'nullable|string',
            'location' => 'nullable|string',
            'branch_id' => 'nullable|exists:branches,id',
        ]);
        BiometricDevice::create($data + [
            'api_token' => BiometricDevice::makeToken(),
            'is_active' => true,
        ]);

        return back()->with('success', 'Device registered. Copy the API token from the list.');
    }

    public function analytics(AnalyticsService $analytics): View
    {
        $data = $analytics->dashboard();

        return view('admin.analytics', $data);
    }

    public function shiftSwaps(): View
    {
        $swaps = ShiftSwapRequest::with(['requester', 'targetUser'])->latest()->get();

        return view('admin.shift-swaps', compact('swaps'));
    }

    public function reviewShiftSwap(Request $request, ShiftSwapRequest $swap)
    {
        $data = $request->validate(['status' => 'required|in:Approved,Rejected']);
        $swap->update(['status' => $data['status'], 'reviewed_by' => Auth::id()]);

        if ($data['status'] === 'Approved') {
            // Swap active assignments for that date range starting that day
            $reqAssign = ShiftAssignment::where('user_id', $swap->requester_id)
                ->whereDate('from_date', '<=', $swap->date)
                ->where(fn ($q) => $q->whereNull('to_date')->orWhereDate('to_date', '>=', $swap->date))
                ->latest('from_date')->first();
            $tgtAssign = ShiftAssignment::where('user_id', $swap->target_user_id)
                ->whereDate('from_date', '<=', $swap->date)
                ->where(fn ($q) => $q->whereNull('to_date')->orWhereDate('to_date', '>=', $swap->date))
                ->latest('from_date')->first();

            if ($reqAssign && $tgtAssign) {
                $rs = $reqAssign->shift_id;
                $ts = $tgtAssign->shift_id;
                $reqAssign->update(['shift_id' => $ts]);
                $tgtAssign->update(['shift_id' => $rs]);
            }
        }

        return back()->with('success', 'Shift swap '.$data['status'].'.');
    }

    // —— Employee self-service ——
    public function myDocuments(): View
    {
        $docs = EmployeeDocument::where('user_id', Auth::id())->latest()->get();

        return view('employee.documents', compact('docs'));
    }

    public function uploadMyDocument(Request $request)
    {
        $data = $request->validate([
            'type' => 'required|in:nid,joining_letter,tin,contract,other',
            'title' => 'required|string|max:255',
            'file' => 'required|file|max:5120|mimes:pdf,jpg,jpeg,png,doc,docx',
        ]);
        $path = $request->file('file')->store('documents', 'public');
        EmployeeDocument::create([
            'user_id' => Auth::id(),
            'type' => $data['type'],
            'title' => $data['title'],
            'file_path' => $path,
            'mime' => $request->file('file')->getMimeType(),
            'size' => $request->file('file')->getSize(),
            'uploaded_by' => Auth::id(),
        ]);

        return back()->with('success', 'Document uploaded.');
    }

    public function myInvestments(): View
    {
        $proofs = InvestmentProof::where('user_id', Auth::id())->latest()->get();

        return view('employee.investments', compact('proofs'));
    }

    public function storeMyInvestment(Request $request)
    {
        $data = $request->validate([
            'fiscal_year' => 'required|integer|min:2020',
            'category' => 'required|in:dps,life_insurance,donation,sanchaypatra,other',
            'amount' => 'required|numeric|min:1',
            'file' => 'nullable|file|max:5120|mimes:pdf,jpg,jpeg,png',
        ]);
        $path = $request->hasFile('file') ? $request->file('file')->store('investments', 'public') : null;
        InvestmentProof::create([
            'user_id' => Auth::id(),
            'fiscal_year' => $data['fiscal_year'],
            'category' => $data['category'],
            'amount' => $data['amount'],
            'file_path' => $path,
            'status' => 'Pending',
        ]);

        return back()->with('success', 'Investment proof submitted for HR review.');
    }

    public function myAppraisals(): View
    {
        $reviews = PerformanceReview::where('user_id', Auth::id())->latest()->get();

        return view('employee.appraisals', compact('reviews'));
    }

    public function myShiftSwaps(): View
    {
        $swaps = ShiftSwapRequest::with(['requester', 'targetUser'])
            ->where(function ($q) {
                $q->where('requester_id', Auth::id())->orWhere('target_user_id', Auth::id());
            })
            ->latest()->get();
        $colleagues = User::where('department', Auth::user()->department)
            ->where('id', '!=', Auth::id())
            ->where('status', 'Active')->orderBy('name')->get();

        return view('employee.shift-swaps', compact('swaps', 'colleagues'));
    }

    public function storeShiftSwap(Request $request)
    {
        $data = $request->validate([
            'target_user_id' => 'required|exists:users,id',
            'date' => 'required|date',
            'reason' => 'nullable|string|max:1000',
        ]);
        ShiftSwapRequest::create([
            'requester_id' => Auth::id(),
            'target_user_id' => $data['target_user_id'],
            'date' => $data['date'],
            'reason' => $data['reason'] ?? null,
            'status' => 'Pending',
        ]);

        return back()->with('success', 'Shift swap requested.');
    }

    public function downloadPayslip(Payslip $payslip)
    {
        abort_unless($payslip->user_id === Auth::id() || Auth::user()->isAdmin(), 403);
        $payslip->load('user');
        $pdf = Pdf::loadView('pdf.payslip', ['p' => $payslip]);

        return $pdf->download('payslip-'.$payslip->month.'.pdf');
    }

    public function emailPayslip(Payslip $payslip)
    {
        abort_unless(Auth::user()->isAdmin() || $payslip->user_id === Auth::id(), 403);
        $payslip->load('user');
        $pdf = Pdf::loadView('pdf.payslip', ['p' => $payslip]);
        $user = $payslip->user;
        if (! $user->email) {
            return back()->with('error', 'Employee has no email.');
        }
        Mail::raw('Please find attached your payslip for '.$payslip->month.'.', function ($message) use ($user, $pdf, $payslip) {
            $message->to($user->email)->subject('Payslip '.$payslip->month)
                ->attachData($pdf->output(), 'payslip.pdf', ['mime' => 'application/pdf']);
        });

        return back()->with('success', 'Payslip emailed to '.$user->email);
    }

    public function setLocale(Request $request)
    {
        $data = $request->validate(['locale' => 'required|in:en,bn']);
        if (Auth::check()) {
            Auth::user()->update(['locale' => $data['locale']]);
        }
        session(['locale' => $data['locale']]);

        return back();
    }

    public function twoFactor(): View
    {
        return view('employee.two-factor', ['user' => Auth::user()]);
    }

    public function toggleTwoFactor(Request $request)
    {
        $enabled = $request->boolean('enabled');
        Auth::user()->update(['two_factor_enabled' => $enabled]);

        return back()->with('success', $enabled ? '2FA enabled. OTP will be required at login.' : '2FA disabled.');
    }
}
