<?php

namespace Modules\HR\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\InvestmentProof;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class InvestmentController extends Controller
{
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
}
