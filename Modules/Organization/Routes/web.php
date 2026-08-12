<?php

use Illuminate\Support\Facades\Route;
use Modules\Organization\Http\Controllers\CompanyController;
use Modules\Organization\Http\Controllers\EmployeeController;

Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/employees', [EmployeeController::class, 'employees'])->name('employees');
    Route::get('/employees/create', [EmployeeController::class, 'createEmployee'])->name('employees.create');
    Route::post('/employees', [EmployeeController::class, 'storeEmployee'])->name('employees.store');
    Route::get('/employees/{employee}/edit', [EmployeeController::class, 'editEmployee'])->name('employees.edit');
    Route::put('/employees/{employee}', [EmployeeController::class, 'updateEmployee'])->name('employees.update');
    Route::delete('/employees/{employee}', [EmployeeController::class, 'destroyEmployee'])->name('employees.destroy');

    Route::get('/departments', [EmployeeController::class, 'departments'])->name('departments');
    Route::post('/departments', [EmployeeController::class, 'storeDepartment'])->name('departments.store');
    Route::put('/departments/{department}', [EmployeeController::class, 'updateDepartment'])->name('departments.update');
    Route::delete('/departments/{department}', [EmployeeController::class, 'destroyDepartment'])->name('departments.destroy');
    Route::post('/designations', [EmployeeController::class, 'storeDesignation'])->name('designations.store');
    Route::put('/designations/{designation}', [EmployeeController::class, 'updateDesignation'])->name('designations.update');
    Route::delete('/designations/{designation}', [EmployeeController::class, 'destroyDesignation'])->name('designations.destroy');

    Route::get('/companies', [CompanyController::class, 'companies'])->name('companies');
    Route::post('/companies', [CompanyController::class, 'storeCompany'])->name('companies.store');
    Route::post('/branches', [CompanyController::class, 'storeBranch'])->name('branches.store');
});
