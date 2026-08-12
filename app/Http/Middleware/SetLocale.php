<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;

class SetLocale
{
    public function handle(Request $request, Closure $next)
    {
        $locale = session('locale')
            ?? Auth::user()?->locale
            ?? config('app.locale', 'en');

        if (! in_array($locale, ['en', 'bn'], true)) {
            $locale = 'en';
        }

        App::setLocale($locale);

        return $next($request);
    }
}
