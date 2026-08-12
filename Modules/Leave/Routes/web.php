<?php

use Illuminate\Support\Facades\Route;
use Modules\Leave\Http\Controllers\EmployeeLeaveController;
use Modules\Leave\Http\Controllers\LeavePolicyController;
use Modules\Leave\Http\Controllers\ManagerLeaveController;

Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/leave-policies', [LeavePolicyController::class, 'leavePolicies'])->name('leave.policies');
    Route::put('/leave-policies/{policy}', [LeavePolicyController::class, 'updateLeavePolicy'])->name('leave.policies.update');
});

Route::middleware(['auth', 'role:employee,manager'])->prefix('employee')->name('employee.')->group(function () {
    Route::get('/leave', [EmployeeLeaveController::class, 'leave'])->name('leave');
    Route::post('/leave', [EmployeeLeaveController::class, 'storeLeave'])->name('leave.store');
    Route::get('/overtime', [EmployeeLeaveController::class, 'overtime'])->name('overtime');
    Route::post('/overtime', [EmployeeLeaveController::class, 'storeOvertime'])->name('overtime.store');
});

Route::middleware(['auth', 'role:manager'])->prefix('manager')->name('manager.')->group(function () {
    Route::get('/leaves', [ManagerLeaveController::class, 'leaves'])->name('leaves');
    Route::post('/leaves/{leave}/review', [ManagerLeaveController::class, 'reviewLeave'])->name('leaves.review');
    Route::post('/leaves/bulk-approve', [ManagerLeaveController::class, 'bulkApproveLeaves'])->name('leaves.bulk');
});
