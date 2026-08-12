<?php

namespace Modules\Attendance\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Shift;
use App\Models\ShiftAssignment;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RosterController extends Controller
{
    public function roster(): View
    {
        $shifts = Shift::withCount('assignments')->get();
        $employees = User::where('role', '!=', 'admin')->where('status', 'Active')->orderBy('name')->get();
        $assignments = ShiftAssignment::with(['user', 'shift'])->latest()->get();

        return view('admin.roster', compact('shifts', 'employees', 'assignments'));
    }

    public function storeShift(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'start_time' => 'required|string',
            'end_time' => 'required|string',
            'grace_minutes' => 'nullable|integer|min:0',
            'break_minutes' => 'nullable|integer|min:0',
            'ot_starts_after' => 'nullable|integer|min:0',
            'is_overnight' => 'nullable|boolean',
        ]);

        $data['grace_minutes'] = $data['grace_minutes'] ?? 0;
        $data['break_minutes'] = $data['break_minutes'] ?? 0;
        $data['ot_starts_after'] = $data['ot_starts_after'] ?? 0;
        $data['is_overnight'] = $request->boolean('is_overnight');

        Shift::create($data);

        return back()->with('success', 'Shift created successfully.');
    }

    public function updateShift(Request $request, Shift $shift)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'start_time' => 'required|string',
            'end_time' => 'required|string',
            'grace_minutes' => 'nullable|integer|min:0',
            'break_minutes' => 'nullable|integer|min:0',
            'ot_starts_after' => 'nullable|integer|min:0',
            'is_overnight' => 'nullable|boolean',
        ]);

        $data['grace_minutes'] = $data['grace_minutes'] ?? 0;
        $data['break_minutes'] = $data['break_minutes'] ?? 0;
        $data['ot_starts_after'] = $data['ot_starts_after'] ?? 0;
        $data['is_overnight'] = $request->boolean('is_overnight');

        $shift->update($data);

        return back()->with('success', 'Shift updated successfully.');
    }

    public function destroyShift(Shift $shift)
    {
        $shift->delete();

        return back()->with('success', 'Shift deleted successfully.');
    }

    public function storeShiftAssignment(Request $request)
    {
        $data = $request->validate([
            'user_id' => 'required|exists:users,id',
            'shift_id' => 'required|exists:shifts,id',
            'from_date' => 'required|date',
            'to_date' => 'nullable|date|after_or_equal:from_date',
        ]);

        ShiftAssignment::create($data);

        return back()->with('success', 'Shift assigned successfully.');
    }

    public function destroyShiftAssignment(ShiftAssignment $assignment)
    {
        $assignment->delete();

        return back()->with('success', 'Shift assignment removed successfully.');
    }
}
