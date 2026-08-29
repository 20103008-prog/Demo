<?php

namespace Modules\Leave\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\LeaveRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class ManagerLeaveController extends Controller
{
    public function leaves(): View
    {
        $dept = Auth::user()->department;
        $leaves = LeaveRequest::with('user')
            ->whereHas('user', fn ($q) => $q->where('department', $dept))
            ->latest()
            ->get();

        return view('manager.leaves', compact('leaves'));
    }

    public function reviewLeave(Request $request, LeaveRequest $leave)
    {
        $this->assertTeamLeave($leave);

        if ($leave->status !== 'Pending') {
            return back()->with('error', 'This leave request has already been reviewed.');
        }

        $data = $request->validate([
            'status' => 'required|in:Approved,Rejected',
            'review_comment' => 'nullable|string|max:500',
        ]);

        $leave->update([
            'status' => $data['status'],
            'review_comment' => $data['review_comment'] ?? null,
            'reviewed_by' => Auth::id(),
        ]);

        AuditLog::create([
            'code' => 'AL'.str_pad((string) (AuditLog::max('id') + 1), 3, '0', STR_PAD_LEFT),
            'action' => 'Leave '.$data['status'],
            'module' => 'Leaves',
            'user_name' => Auth::user()->name,
            'role' => 'Manager',
            'details' => $data['status'].' leave '.$leave->code.' for '.$leave->user->name,
            'severity' => $data['status'] === 'Rejected' ? 'warning' : 'info',
            'logged_at' => now(),
        ]);

        return back()->with('success', 'Leave '.$data['status'].'.');
    }

    public function bulkApproveLeaves(Request $request)
    {
        $ids = $request->validate(['ids' => 'required|array', 'ids.*' => 'integer'])['ids'];
        LeaveRequest::whereIn('id', $ids)
            ->where('status', 'Pending')
            ->whereHas('user', fn ($q) => $q->where('department', Auth::user()->department))
            ->update([
                'status' => 'Approved',
                'reviewed_by' => Auth::id(),
            ]);

        return back()->with('success', 'Selected leaves approved.');
    }

    protected function assertTeamLeave(LeaveRequest $leave): void
    {
        $leave->loadMissing('user');

        if (! $leave->user || $leave->user->department !== Auth::user()->department) {
            abort(403, 'You can only review leave for your own team.');
        }
    }
}
