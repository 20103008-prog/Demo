<?php

use Illuminate\Support\Facades\Route;
use Modules\Payroll\Http\Controllers\EmployeePayrollController;
use Modules\Payroll\Http\Controllers\PayrollApprovalController;
use Modules\Payroll\Http\Controllers\PayrollController;

Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/payroll', [PayrollController::class, 'payroll'])->name('payroll');
    Route::post('/payroll/process', [PayrollController::class, 'processPayroll'])->name('payroll.process');
    Route::get('/payroll-approvals', [PayrollApprovalController::class, 'payrollApprovals'])->name('payroll.approvals');
    Route::post('/payroll-approvals/{run}', [PayrollApprovalController::class, 'approvePayroll'])->name('payroll.approve');
    Route::get('/bank-advice', [PayrollApprovalController::class, 'bankAdvice'])->name('bank.advice');
    Route::get('/tax-pf', [PayrollController::class, 'taxPf'])->name('taxpf');
    Route::get('/loans', [PayrollController::class, 'loans'])->name('loans');
    Route::post('/loans', [PayrollController::class, 'storeLoan'])->name('loans.store');
    Route::get('/bonus', [PayrollController::class, 'bonus'])->name('bonus');
    Route::post('/bonus', [PayrollController::class, 'storeFestivalBonus'])->name('bonus.store');
    Route::post('/bonus/{bonus}', [PayrollController::class, 'updateBonusStatus'])->name('bonus.update');
    Route::post('/increment', [PayrollController::class, 'storeIncrement'])->name('increment.store');
    Route::post('/increment/{increment}', [PayrollController::class, 'updateIncrementStatus'])->name('increment.update');
    Route::get('/settlement', [PayrollController::class, 'settlement'])->name('settlement');
    Route::post('/settlement/{settlement}', [PayrollController::class, 'updateSettlement'])->name('settlement.update');
    Route::get('/employees/{employee}/settlement', [PayrollController::class, 'prepareSettlement'])->name('employees.settlement');
    Route::post('/employees/{employee}/settle', [PayrollController::class, 'finalizeSettlement'])->name('employees.settle');
    Route::post('/payslip/{payslip}/email', [PayrollApprovalController::class, 'emailPayslip'])->name('payslip.email');
});

Route::middleware(['auth', 'role:employee,manager'])->prefix('employee')->name('employee.')->group(function () {
    Route::get('/payroll', [EmployeePayrollController::class, 'payroll'])->name('payroll');
    Route::get('/payslip/{payslip}/pdf', [PayrollApprovalController::class, 'downloadPayslip'])->name('payslip.pdf');
});
