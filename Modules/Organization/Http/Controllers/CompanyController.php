<?php

namespace Modules\Organization\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\Company;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CompanyController extends Controller
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
}
