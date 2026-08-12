<?php

namespace Modules\HR\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\EmployeeDocument;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class DocumentController extends Controller
{
    public function documents(): View
    {
        $docs = EmployeeDocument::with('user')->latest()->limit(200)->get();
        $employees = User::where('role', '!=', 'admin')->orderBy('name')->get();

        return view('admin.documents', compact('docs', 'employees'));
    }

    public function storeDocument(Request $request)
    {
        $data = $request->validate([
            'user_id' => 'required|exists:users,id',
            'type' => 'required|in:nid,joining_letter,tin,contract,other',
            'title' => 'required|string|max:255',
            'file' => 'required|file|max:5120|mimes:pdf,jpg,jpeg,png,doc,docx',
        ]);
        $path = $request->file('file')->store('documents', 'public');
        EmployeeDocument::create([
            'user_id' => $data['user_id'],
            'type' => $data['type'],
            'title' => $data['title'],
            'file_path' => $path,
            'mime' => $request->file('file')->getMimeType(),
            'size' => $request->file('file')->getSize(),
            'uploaded_by' => Auth::id(),
        ]);

        return back()->with('success', 'Document uploaded.');
    }

    public function myDocuments(): View
    {
        $docs = EmployeeDocument::where('user_id', Auth::id())->latest()->get();

        return view('employee.documents', compact('docs'));
    }

    public function uploadMyDocument(Request $request)
    {
        $data = $request->validate([
            'type' => 'required|in:nid,joining_letter,tin,contract,other',
            'title' => 'required|string|max:255',
            'file' => 'required|file|max:5120|mimes:pdf,jpg,jpeg,png,doc,docx',
        ]);
        $path = $request->file('file')->store('documents', 'public');
        EmployeeDocument::create([
            'user_id' => Auth::id(),
            'type' => $data['type'],
            'title' => $data['title'],
            'file_path' => $path,
            'mime' => $request->file('file')->getMimeType(),
            'size' => $request->file('file')->getSize(),
            'uploaded_by' => Auth::id(),
        ]);

        return back()->with('success', 'Document uploaded.');
    }
}
