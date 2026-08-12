<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\User;
use App\Support\RoleRedirector;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    public function create(): View
    {
        return view('auth.login');
    }

    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();
        $request->session()->regenerate();

        $user = $request->user();
        if ($user->two_factor_enabled) {
            $code = (string) random_int(100000, 999999);
            $user->forceFill([
                'two_factor_code' => $code,
                'two_factor_expires_at' => now()->addMinutes(10),
            ])->save();

            if ($user->email) {
                Mail::raw("Your HR Payroll login OTP is: {$code}\nValid for 10 minutes.", function ($m) use ($user) {
                    $m->to($user->email)->subject('Login OTP');
                });
            }

            Auth::logout();
            $request->session()->put('2fa_user_id', $user->id);

            return redirect()->route('two-factor.challenge')
                ->with('success', 'OTP sent to your email.');
        }

        return redirect()->intended(RoleRedirector::home($user));
    }

    public function challenge(): View|RedirectResponse
    {
        if (! session('2fa_user_id')) {
            return redirect()->route('login');
        }

        return view('auth.two-factor-challenge');
    }

    public function verifyChallenge(Request $request): RedirectResponse
    {
        $data = $request->validate(['code' => 'required|string']);
        $userId = session('2fa_user_id');
        $user = User::find($userId);
        if (! $user || ! $user->two_factor_code || $user->two_factor_expires_at?->isPast()
            || $user->two_factor_code !== $data['code']) {
            return back()->withErrors(['code' => 'Invalid or expired OTP.']);
        }

        $user->forceFill(['two_factor_code' => null, 'two_factor_expires_at' => null])->save();
        $request->session()->forget('2fa_user_id');
        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->intended(RoleRedirector::home($user));
    }

    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
