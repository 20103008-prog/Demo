<?php

namespace Modules\HR\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Increment;
use App\Models\PerformanceReview;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AppraisalController extends Controller
{
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

    public function myAppraisals(): View
    {
        $reviews = PerformanceReview::where('user_id', Auth::id())->latest()->get();

        return view('employee.appraisals', compact('reviews'));
    }
}
