<?php

use Illuminate\Support\Facades\Route;
use Modules\Attendance\Http\Controllers\Api\DeviceApiController;
use Modules\Attendance\Http\Controllers\BiometricController;
use Modules\Attendance\Http\Controllers\EmployeeAttendanceController;
use Modules\Attendance\Http\Controllers\ManagerAttendanceController;
use Modules\Attendance\Http\Controllers\RosterController;
use Modules\Attendance\Http\Controllers\ShiftSwapController;

Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/roster', [RosterController::class, 'roster'])->name('roster');
    Route::post('/shifts', [RosterController::class, 'storeShift'])->name('shifts.store');
    Route::put('/shifts/{shift}', [RosterController::class, 'updateShift'])->name('shifts.update');
    Route::delete('/shifts/{shift}', [RosterController::class, 'destroyShift'])->name('shifts.destroy');
    Route::post('/shift-assignments', [RosterController::class, 'storeShiftAssignment'])->name('shift.assignments.store');
    Route::delete('/shift-assignments/{assignment}', [RosterController::class, 'destroyShiftAssignment'])->name('shift.assignments.destroy');

    Route::get('/biometrics', [BiometricController::class, 'biometrics'])->name('biometrics');
    Route::post('/biometrics', [BiometricController::class, 'storeDevice'])->name('biometrics.store');

    Route::get('/shift-swaps', [ShiftSwapController::class, 'shiftSwaps'])->name('swaps');
    Route::post('/shift-swaps/{swap}', [ShiftSwapController::class, 'reviewShiftSwap'])->name('swaps.review');
});

Route::middleware(['auth', 'role:employee,manager'])->prefix('employee')->name('employee.')->group(function () {
    Route::get('/attendance', [EmployeeAttendanceController::class, 'attendance'])->name('attendance');
    Route::post('/attendance/punch', [EmployeeAttendanceController::class, 'punch'])->name('attendance.punch');
    Route::post('/attendance/offline', [DeviceApiController::class, 'offlinePunch'])->name('attendance.offline');
    Route::get('/shift-swaps', [ShiftSwapController::class, 'myShiftSwaps'])->name('swaps');
    Route::post('/shift-swaps', [ShiftSwapController::class, 'storeShiftSwap'])->name('swaps.store');
});

Route::middleware(['auth', 'role:manager'])->prefix('manager')->name('manager.')->group(function () {
    Route::get('/dashboard', [ManagerAttendanceController::class, 'dashboard'])->name('dashboard');
    Route::get('/team', [ManagerAttendanceController::class, 'team'])->name('team');
    Route::get('/attendance', [ManagerAttendanceController::class, 'attendance'])->name('attendance');
    Route::get('/overtime', [ManagerAttendanceController::class, 'overtime'])->name('overtime');
    Route::post('/overtime/{overtime}/review', [ManagerAttendanceController::class, 'reviewOvertime'])->name('overtime.review');
});
