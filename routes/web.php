<?php

use App\Http\Controllers\ProfileController;
use App\Support\RoleRedirector;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

// Shared application routes. Domain routes live in Modules/{Name}/Routes.

Route::get('/dashboard', function () {
    return redirect(RoleRedirector::home(Auth::user()));
})->middleware(['auth'])->name('dashboard');

Route::middleware(['auth', 'role:admin'])->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
});
