<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\Api\DeviceApiController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\IndustrialController;
use App\Http\Controllers\ManagerController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SiteController;
use App\Support\RoleRedirector;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', [SiteController::class, 'home'])->name('site.home');
Route::get('/products', [SiteController::class, 'products'])->name('site.products');
Route::get('/products/{slug}', [SiteController::class, 'product'])->name('site.product');
Route::get('/contact', [SiteController::class, 'contact'])->name('site.contact');
Route::post('/contact', [SiteController::class, 'storeInquiry'])->name('site.inquiry');
Route::post('/locale', [IndustrialController::class, 'setLocale'])->name('locale.set');

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
    Route::post('/attendance/offline', [DeviceApiController::class, 'offlinePunch'])->name('attendance.offline');
    Route::get('/leave', [EmployeeController::class, 'leave'])->name('leave');
    Route::post('/leave', [EmployeeController::class, 'storeLeave'])->name('leave.store');
    Route::get('/overtime', [EmployeeController::class, 'overtime'])->name('overtime');
    Route::post('/overtime', [EmployeeController::class, 'storeOvertime'])->name('overtime.store');
    Route::get('/payroll', [EmployeeController::class, 'payroll'])->name('payroll');
    Route::get('/payslip/{payslip}/pdf', [IndustrialController::class, 'downloadPayslip'])->name('payslip.pdf');
    Route::get('/queries', [EmployeeController::class, 'queries'])->name('queries');
    Route::post('/queries', [EmployeeController::class, 'storeQuery'])->name('queries.store');
    Route::get('/documents', [IndustrialController::class, 'myDocuments'])->name('documents');
    Route::post('/documents', [IndustrialController::class, 'uploadMyDocument'])->name('documents.store');
    Route::get('/investments', [IndustrialController::class, 'myInvestments'])->name('investments');
    Route::post('/investments', [IndustrialController::class, 'storeMyInvestment'])->name('investments.store');
    Route::get('/appraisals', [IndustrialController::class, 'myAppraisals'])->name('appraisals');
    Route::get('/shift-swaps', [IndustrialController::class, 'myShiftSwaps'])->name('swaps');
    Route::post('/shift-swaps', [IndustrialController::class, 'storeShiftSwap'])->name('swaps.store');
    Route::get('/two-factor', [IndustrialController::class, 'twoFactor'])->name('twofactor');
    Route::post('/two-factor', [IndustrialController::class, 'toggleTwoFactor'])->name('twofactor.toggle');
});

Route::middleware(['auth', 'role:manager'])->prefix('manager')->name('manager.')->group(function () {
    Route::get('/dashboard', [ManagerController::class, 'dashboard'])->name('dashboard');
    Route::get('/team', [ManagerController::class, 'team'])->name('team');
    Route::get('/attendance', [ManagerController::class, 'attendance'])->name('attendance');
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
    Route::get('/roster', [AdminController::class, 'roster'])->name('roster');
    Route::post('/shifts', [AdminController::class, 'storeShift'])->name('shifts.store');
    Route::put('/shifts/{shift}', [AdminController::class, 'updateShift'])->name('shifts.update');
    Route::delete('/shifts/{shift}', [AdminController::class, 'destroyShift'])->name('shifts.destroy');
    Route::post('/shift-assignments', [AdminController::class, 'storeShiftAssignment'])->name('shift.assignments.store');
    Route::delete('/shift-assignments/{assignment}', [AdminController::class, 'destroyShiftAssignment'])->name('shift.assignments.destroy');
    Route::get('/departments', [AdminController::class, 'departments'])->name('departments');
    Route::post('/departments', [AdminController::class, 'storeDepartment'])->name('departments.store');
    Route::put('/departments/{department}', [AdminController::class, 'updateDepartment'])->name('departments.update');
    Route::delete('/departments/{department}', [AdminController::class, 'destroyDepartment'])->name('departments.destroy');
    Route::post('/designations', [AdminController::class, 'storeDesignation'])->name('designations.store');
    Route::put('/designations/{designation}', [AdminController::class, 'updateDesignation'])->name('designations.update');
    Route::delete('/designations/{designation}', [AdminController::class, 'destroyDesignation'])->name('designations.destroy');
    Route::get('/employees/{employee}/history', [AdminController::class, 'employeeHistory'])->name('employees.history');
    Route::post('/employees', [AdminController::class, 'storeEmployee'])->name('employees.store');
    Route::get('/employees/{employee}/edit', [AdminController::class, 'editEmployee'])->name('employees.edit');
    Route::put('/employees/{employee}', [AdminController::class, 'updateEmployee'])->name('employees.update');
    Route::get('/employees/{employee}/settlement', [AdminController::class, 'prepareSettlement'])->name('employees.settlement');
    Route::post('/employees/{employee}/settle', [AdminController::class, 'finalizeSettlement'])->name('employees.settle');
    Route::delete('/employees/{employee}', [AdminController::class, 'destroyEmployee'])->name('employees.destroy');
    Route::get('/payroll', [AdminController::class, 'payroll'])->name('payroll');
    Route::post('/payroll/process', [AdminController::class, 'processPayroll'])->name('payroll.process');
    Route::get('/payroll-approvals', [IndustrialController::class, 'payrollApprovals'])->name('payroll.approvals');
    Route::post('/payroll-approvals/{run}', [IndustrialController::class, 'approvePayroll'])->name('payroll.approve');
    Route::get('/bank-advice', [IndustrialController::class, 'bankAdvice'])->name('bank.advice');
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
    Route::get('/analytics', [IndustrialController::class, 'analytics'])->name('analytics');
    Route::get('/companies', [IndustrialController::class, 'companies'])->name('companies');
    Route::post('/companies', [IndustrialController::class, 'storeCompany'])->name('companies.store');
    Route::post('/branches', [IndustrialController::class, 'storeBranch'])->name('branches.store');
    Route::get('/leave-policies', [IndustrialController::class, 'leavePolicies'])->name('leave.policies');
    Route::put('/leave-policies/{policy}', [IndustrialController::class, 'updateLeavePolicy'])->name('leave.policies.update');
    Route::get('/documents', [IndustrialController::class, 'documents'])->name('documents');
    Route::post('/documents', [IndustrialController::class, 'storeDocument'])->name('documents.store');
    Route::get('/investments', [IndustrialController::class, 'investments'])->name('investments');
    Route::post('/investments/{proof}', [IndustrialController::class, 'reviewInvestment'])->name('investments.review');
    Route::get('/appraisals', [IndustrialController::class, 'appraisals'])->name('appraisals');
    Route::post('/appraisals', [IndustrialController::class, 'storeAppraisal'])->name('appraisals.store');
    Route::post('/appraisals/{review}/apply', [IndustrialController::class, 'applyAppraisal'])->name('appraisals.apply');
    Route::get('/biometrics', [IndustrialController::class, 'biometrics'])->name('biometrics');
    Route::post('/biometrics', [IndustrialController::class, 'storeDevice'])->name('biometrics.store');
    Route::get('/shift-swaps', [IndustrialController::class, 'shiftSwaps'])->name('swaps');
    Route::post('/shift-swaps/{swap}', [IndustrialController::class, 'reviewShiftSwap'])->name('swaps.review');
    Route::post('/payslip/{payslip}/email', [IndustrialController::class, 'emailPayslip'])->name('payslip.email');
    Route::get('/products', [AdminController::class, 'products'])->name('products');
    Route::post('/products/{product}', [AdminController::class, 'updateProduct'])->name('products.update');
    Route::get('/inquiries', [AdminController::class, 'inquiries'])->name('inquiries');
    Route::post('/inquiries/{inquiry}', [AdminController::class, 'updateInquiry'])->name('inquiries.update');
    Route::get('/audit', [AdminController::class, 'audit'])->name('audit');
});

require __DIR__.'/auth.php';
