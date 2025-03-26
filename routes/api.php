<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProductImageController;

// Nhóm API Routes
Route::middleware('api')->group(function () {
    Route::apiResource('products', ProductController::class);

    // API cho hình ảnh sản phẩm
    Route::post('/products/{id}/upload-image', [ProductImageController::class, 'uploadImage']);
    Route::get('/products/{id}/images', [ProductImageController::class, 'getProductImages']);
    Route::delete('/product-images/{id}', [ProductImageController::class, 'deleteImage']);
});

