<?php

namespace Modules\Auth\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class TwoFactorController extends Controller
{
    public function twoFactor(): View
    {
        return view('employee.two-factor', ['user' => Auth::user()]);
    }

    public function toggleTwoFactor(Request $request)
    {
        $enabled = $request->boolean('enabled');
        Auth::user()->update(['two_factor_enabled' => $enabled]);

        return back()->with('success', $enabled ? '2FA enabled. OTP will be required at login.' : '2FA disabled.');
    }
}
