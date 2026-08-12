<?php

use Illuminate\Support\Facades\Route;
use Modules\Analytics\Http\Controllers\AnalyticsController;
use Modules\Analytics\Http\Controllers\DashboardController;
use Modules\Analytics\Http\Controllers\EmployeeDashboardController;
use Modules\Analytics\Http\Controllers\ManagerReportController;

Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'dashboard'])->name('dashboard');
    Route::get('/today-punches', [DashboardController::class, 'todayPunches'])->name('today.punches');
    Route::get('/today-summary', [DashboardController::class, 'todaySummary'])->name('today.summary');
    Route::get('/today-not-punched', [DashboardController::class, 'notPunchedToday'])->name('today.not.punched');
    Route::get('/late-today', [DashboardController::class, 'lateToday'])->name('late.today');
    Route::get('/employees/{employee}/history', [DashboardController::class, 'employeeHistory'])->name('employees.history');
    Route::get('/reports', [DashboardController::class, 'reports'])->name('reports');
    Route::get('/analytics', [AnalyticsController::class, 'analytics'])->name('analytics');
    Route::get('/audit', [DashboardController::class, 'audit'])->name('audit');
});

Route::middleware(['auth', 'role:employee,manager'])->prefix('employee')->name('employee.')->group(function () {
    Route::get('/dashboard', [EmployeeDashboardController::class, 'dashboard'])->name('dashboard');
});

Route::middleware(['auth', 'role:manager'])->prefix('manager')->name('manager.')->group(function () {
    Route::get('/reports', [ManagerReportController::class, 'reports'])->name('reports');
});
