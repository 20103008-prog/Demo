<?php

use Illuminate\Support\Facades\Route;
use Modules\Site\Http\Controllers\AdminProductController;
use Modules\Site\Http\Controllers\SiteController;

Route::get('/', [SiteController::class, 'home'])->name('site.home');
Route::get('/products', [SiteController::class, 'products'])->name('site.products');
Route::get('/products/{slug}', [SiteController::class, 'product'])->name('site.product');
Route::get('/contact', [SiteController::class, 'contact'])->name('site.contact');
Route::post('/contact', [SiteController::class, 'storeInquiry'])->name('site.inquiry');

Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/products', [AdminProductController::class, 'products'])->name('products');
    Route::post('/products/{product}', [AdminProductController::class, 'updateProduct'])->name('products.update');
    Route::get('/inquiries', [AdminProductController::class, 'inquiries'])->name('inquiries');
    Route::post('/inquiries/{inquiry}', [AdminProductController::class, 'updateInquiry'])->name('inquiries.update');
});
