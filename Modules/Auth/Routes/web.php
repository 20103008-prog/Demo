<?php

use Illuminate\Support\Facades\Route;
use Modules\Auth\Http\Controllers\Auth\AuthenticatedSessionController;
use Modules\Auth\Http\Controllers\Auth\NewPasswordController;
use Modules\Auth\Http\Controllers\Auth\PasswordController;
use Modules\Auth\Http\Controllers\Auth\PasswordResetLinkController;
use Modules\Auth\Http\Controllers\TwoFactorController;

Route::middleware('guest')->group(function () {
    Route::get('login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('login', [AuthenticatedSessionController::class, 'store']);

    Route::get('two-factor-challenge', [AuthenticatedSessionController::class, 'challenge'])->name('two-factor.challenge');
    Route::post('two-factor-challenge', [AuthenticatedSessionController::class, 'verifyChallenge'])->name('two-factor.verify');

    Route::get('forgot-password', [PasswordResetLinkController::class, 'create'])->name('password.request');
    Route::post('forgot-password', [PasswordResetLinkController::class, 'store'])->name('password.email');

    Route::get('reset-password/{token}', [NewPasswordController::class, 'create'])->name('password.reset');
    Route::post('reset-password', [NewPasswordController::class, 'store'])->name('password.store');
});

Route::middleware('auth')->group(function () {
    Route::put('password', [PasswordController::class, 'update'])->name('password.update');
    Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');
});

Route::middleware(['auth', 'role:employee,manager'])->prefix('employee')->name('employee.')->group(function () {
    Route::get('/two-factor', [TwoFactorController::class, 'twoFactor'])->name('twofactor');
    Route::post('/two-factor', [TwoFactorController::class, 'toggleTwoFactor'])->name('twofactor.toggle');
});
