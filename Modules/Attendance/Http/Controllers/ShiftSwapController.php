<?php

namespace Modules\Attendance\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Shift;
use App\Models\ShiftAssignment;
use App\Models\ShiftSwapRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class ShiftSwapController extends Controller
{
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

    public function myShiftSwaps(): View
    {
        $swaps = ShiftSwapRequest::with(['requester', 'targetUser'])
            ->where(function ($q) {
                $q->where('requester_id', Auth::id())->orWhere('target_user_id', Auth::id());
            })
            ->latest()->get();
        $colleagues = User::where('department', Auth::user()->department)
            ->where('id', '!=', Auth::id())
            ->whereIn('role', ['employee', 'manager'])
            ->where('status', 'Active')->orderBy('name')->get();

        return view('employee.shift-swaps', compact('swaps', 'colleagues'));
    }

    public function storeShiftSwap(Request $request)
    {
        $user = Auth::user();

        $data = $request->validate([
            'target_user_id' => 'required|exists:users,id',
            'date' => 'required|date|after_or_equal:today',
            'reason' => 'nullable|string|max:1000',
        ], [
            'date.after_or_equal' => 'Shift swap cannot be requested for a past date.',
        ]);

        if ((int) $data['target_user_id'] === (int) $user->id) {
            return back()->withErrors(['target_user_id' => 'You cannot swap a shift with yourself.'])->withInput();
        }

        $target = User::where('id', $data['target_user_id'])
            ->where('department', $user->department)
            ->where('status', 'Active')
            ->whereIn('role', ['employee', 'manager'])
            ->first();

        if (! $target) {
            return back()->withErrors(['target_user_id' => 'You can only swap with an active colleague in your department.'])->withInput();
        }

        $duplicate = ShiftSwapRequest::where('requester_id', $user->id)
            ->whereDate('date', $data['date'])
            ->where('status', 'Pending')
            ->exists();

        if ($duplicate) {
            return back()->withErrors(['date' => 'You already have a pending swap for this date.'])->withInput();
        }

        ShiftSwapRequest::create([
            'requester_id' => $user->id,
            'target_user_id' => $target->id,
            'date' => $data['date'],
            'reason' => $data['reason'] ?? null,
            'status' => 'Pending',
        ]);

        return back()->with('success', 'Shift swap requested.');
    }
}
