<?php

use App\Http\Controllers\ContactController;
use App\Http\Controllers\ProductImageController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('home/index');
});

// Trang liên hệ
Route::get('/lien-he', [ContactController::class, 'index'])->name('contact.index');
Route::post('/lien-he', [ContactController::class, 'store'])->name('contact.store');

// API quản lý hình ảnh sản phẩm (Sử dụng trên web)
Route::post('/products/{id}/upload-image', [ProductImageController::class, 'uploadImage']);
Route::get('/products/{id}/images', [ProductImageController::class, 'getProductImages']);
Route::delete('/product-images/{id}', [ProductImageController::class, 'deleteImage']);
