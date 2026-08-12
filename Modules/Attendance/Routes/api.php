<?php

use Illuminate\Support\Facades\Route;
use Modules\Attendance\Http\Controllers\Api\DeviceApiController;

Route::post('/biometric/punches', [DeviceApiController::class, 'syncPunches']);
Route::get('/biometric/employees', [DeviceApiController::class, 'employees']);
