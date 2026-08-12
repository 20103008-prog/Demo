<?php

namespace Modules\HR\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\HrQuery;
use App\Services\AiQueryService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class EmployeeQueryController extends Controller
{
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
