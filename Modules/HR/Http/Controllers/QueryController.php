<?php

namespace Modules\HR\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\HrQuery;
use Illuminate\Http\Request;
use Illuminate\View\View;

class QueryController extends Controller
{
    public function queries(): View
    {
        $queries = HrQuery::with('user')->latest()->get();

        return view('admin.queries', compact('queries'));
    }

    public function replyQuery(Request $request, HrQuery $query)
    {
        $data = $request->validate(['response' => 'required|string|max:5000']);
        $query->update([
            'response' => $data['response'],
            'status' => 'Resolved',
        ]);

        return back()->with('success', 'Reply sent and query resolved.');
    }
}
