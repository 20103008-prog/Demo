<?php

namespace Modules\Payroll\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\PayrollRun;
use App\Models\Payslip;
use App\Services\PayrollService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PayrollApprovalController extends Controller
{
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
}
