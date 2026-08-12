<?php

namespace Modules\Leave\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\LeavePolicy;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LeavePolicyController extends Controller
{
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
}
