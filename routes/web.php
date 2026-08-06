<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\ManagerController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SiteController;
use App\Support\RoleRedirector;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

// Public marketing website (clients)
Route::get('/', [SiteController::class, 'home'])->name('site.home');
Route::get('/products', [SiteController::class, 'products'])->name('site.products');
Route::get('/products/{slug}', [SiteController::class, 'product'])->name('site.product');
Route::get('/contact', [SiteController::class, 'contact'])->name('site.contact');
Route::post('/contact', [SiteController::class, 'storeInquiry'])->name('site.inquiry');

Route::get('/dashboard', function () {
    return redirect(RoleRedirector::home(Auth::user()));
})->middleware(['auth'])->name('dashboard');

Route::middleware(['auth', 'role:admin'])->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
});

Route::middleware(['auth', 'role:employee,manager'])->prefix('employee')->name('employee.')->group(function () {
    Route::get('/dashboard', [EmployeeController::class, 'dashboard'])->name('dashboard');
    Route::get('/attendance', [EmployeeController::class, 'attendance'])->name('attendance');
    Route::post('/attendance/punch', [EmployeeController::class, 'punch'])->name('attendance.punch');
    Route::get('/leave', [EmployeeController::class, 'leave'])->name('leave');
    Route::post('/leave', [EmployeeController::class, 'storeLeave'])->name('leave.store');
    Route::get('/payroll', [EmployeeController::class, 'payroll'])->name('payroll');
    Route::get('/queries', [EmployeeController::class, 'queries'])->name('queries');
    Route::post('/queries', [EmployeeController::class, 'storeQuery'])->name('queries.store');
});

Route::middleware(['auth', 'role:manager'])->prefix('manager')->name('manager.')->group(function () {
    Route::get('/dashboard', [ManagerController::class, 'dashboard'])->name('dashboard');
    Route::get('/team', [ManagerController::class, 'team'])->name('team');
    Route::get('/leaves', [ManagerController::class, 'leaves'])->name('leaves');
    Route::post('/leaves/{leave}/review', [ManagerController::class, 'reviewLeave'])->name('leaves.review');
    Route::post('/leaves/bulk-approve', [ManagerController::class, 'bulkApproveLeaves'])->name('leaves.bulk');
    Route::get('/overtime', [ManagerController::class, 'overtime'])->name('overtime');
    Route::post('/overtime/{overtime}/review', [ManagerController::class, 'reviewOvertime'])->name('overtime.review');
    Route::get('/reports', [ManagerController::class, 'reports'])->name('reports');
});

Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('dashboard');
    Route::get('/today-punches', [AdminController::class, 'todayPunches'])->name('today.punches');
    Route::get('/today-summary', [AdminController::class, 'todaySummary'])->name('today.summary');
    Route::get('/today-not-punched', [AdminController::class, 'notPunchedToday'])->name('today.not.punched');
    Route::get('/late-today', [AdminController::class, 'lateToday'])->name('late.today');
    Route::get('/employees', [AdminController::class, 'employees'])->name('employees');
    Route::get('/employees/create', [AdminController::class, 'createEmployee'])->name('employees.create');
    Route::get('/employees/{employee}/history', [AdminController::class, 'employeeHistory'])->name('employees.history');
    Route::post('/employees', [AdminController::class, 'storeEmployee'])->name('employees.store');
    Route::get('/employees/{employee}/edit', [AdminController::class, 'editEmployee'])->name('employees.edit');
    Route::put('/employees/{employee}', [AdminController::class, 'updateEmployee'])->name('employees.update');
    Route::get('/employees/{employee}/settlement', [AdminController::class, 'prepareSettlement'])->name('employees.settlement');
    Route::post('/employees/{employee}/settle', [AdminController::class, 'finalizeSettlement'])->name('employees.settle');
    Route::delete('/employees/{employee}', [AdminController::class, 'destroyEmployee'])->name('employees.destroy');
    Route::get('/payroll', [AdminController::class, 'payroll'])->name('payroll');
    Route::post('/payroll/process', [AdminController::class, 'processPayroll'])->name('payroll.process');
    Route::get('/tax-pf', [AdminController::class, 'taxPf'])->name('taxpf');
    Route::get('/loans', [AdminController::class, 'loans'])->name('loans');
    Route::post('/loans', [AdminController::class, 'storeLoan'])->name('loans.store');
    Route::get('/bonus', [AdminController::class, 'bonus'])->name('bonus');
    Route::post('/bonus', [AdminController::class, 'storeFestivalBonus'])->name('bonus.store');
    Route::post('/bonus/{bonus}', [AdminController::class, 'updateBonusStatus'])->name('bonus.update');
    Route::post('/increment', [AdminController::class, 'storeIncrement'])->name('increment.store');
    Route::post('/increment/{increment}', [AdminController::class, 'updateIncrementStatus'])->name('increment.update');
    Route::get('/settlement', [AdminController::class, 'settlement'])->name('settlement');
    Route::post('/settlement/{settlement}', [AdminController::class, 'updateSettlement'])->name('settlement.update');
    Route::get('/queries', [AdminController::class, 'queries'])->name('queries');
    Route::post('/queries/{query}/reply', [AdminController::class, 'replyQuery'])->name('queries.reply');
    Route::get('/reports', [AdminController::class, 'reports'])->name('reports');
    Route::get('/products', [AdminController::class, 'products'])->name('products');
    Route::post('/products/{product}', [AdminController::class, 'updateProduct'])->name('products.update');
    Route::get('/inquiries', [AdminController::class, 'inquiries'])->name('inquiries');
    Route::post('/inquiries/{inquiry}', [AdminController::class, 'updateInquiry'])->name('inquiries.update');
    Route::get('/audit', [AdminController::class, 'audit'])->name('audit');
});

require __DIR__.'/auth.php';
