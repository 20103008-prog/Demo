<?php

namespace Modules\HR\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LocaleController extends Controller
{
    public function setLocale(Request $request)
    {
        $data = $request->validate(['locale' => 'required|in:en,bn']);
        if (Auth::check()) {
            Auth::user()->update(['locale' => $data['locale']]);
        }
        session(['locale' => $data['locale']]);

        return back();
    }
}
