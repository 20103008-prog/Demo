<?php

use Illuminate\Support\Facades\Route;
use Modules\HR\Http\Controllers\AppraisalController;
use Modules\HR\Http\Controllers\DocumentController;
use Modules\HR\Http\Controllers\EmployeeQueryController;
use Modules\HR\Http\Controllers\InvestmentController;
use Modules\HR\Http\Controllers\LocaleController;
use Modules\HR\Http\Controllers\QueryController;

Route::post('/locale', [LocaleController::class, 'setLocale'])->name('locale.set');

Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/documents', [DocumentController::class, 'documents'])->name('documents');
    Route::post('/documents', [DocumentController::class, 'storeDocument'])->name('documents.store');
    Route::get('/investments', [InvestmentController::class, 'investments'])->name('investments');
    Route::post('/investments/{proof}', [InvestmentController::class, 'reviewInvestment'])->name('investments.review');
    Route::get('/appraisals', [AppraisalController::class, 'appraisals'])->name('appraisals');
    Route::post('/appraisals', [AppraisalController::class, 'storeAppraisal'])->name('appraisals.store');
    Route::post('/appraisals/{review}/apply', [AppraisalController::class, 'applyAppraisal'])->name('appraisals.apply');
    Route::get('/queries', [QueryController::class, 'queries'])->name('queries');
    Route::post('/queries/{query}/reply', [QueryController::class, 'replyQuery'])->name('queries.reply');
});

Route::middleware(['auth', 'role:employee,manager'])->prefix('employee')->name('employee.')->group(function () {
    Route::get('/documents', [DocumentController::class, 'myDocuments'])->name('documents');
    Route::post('/documents', [DocumentController::class, 'uploadMyDocument'])->name('documents.store');
    Route::get('/investments', [InvestmentController::class, 'myInvestments'])->name('investments');
    Route::post('/investments', [InvestmentController::class, 'storeMyInvestment'])->name('investments.store');
    Route::get('/appraisals', [AppraisalController::class, 'myAppraisals'])->name('appraisals');
    Route::get('/queries', [EmployeeQueryController::class, 'queries'])->name('queries');
    Route::post('/queries', [EmployeeQueryController::class, 'storeQuery'])->name('queries.store');
});
