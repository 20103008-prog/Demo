<?php

use App\Http\Controllers\Api\DeviceApiController;
use Illuminate\Support\Facades\Route;

Route::post('/biometric/punches', [DeviceApiController::class, 'syncPunches']);
Route::get('/biometric/employees', [DeviceApiController::class, 'employees']);
