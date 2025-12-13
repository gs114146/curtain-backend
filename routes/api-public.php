<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\PublicApiController;

/*
|--------------------------------------------------------------------------
| Public API Routes
|--------------------------------------------------------------------------
|
| These routes are accessible without authentication and are designed
| for frontend applications to consume product data.
|
*/

// Product listing and search
Route::get('/products', [PublicApiController::class, 'getProducts'])->name('api.public.products');

// Get featured products
Route::get('/products/featured', [PublicApiController::class, 'getFeaturedProducts'])->name('api.public.featured');

// Get single product
Route::get('/products/{id}', [PublicApiController::class, 'getProduct'])->name('api.public.product');

// Get filter options
Route::get('/filters', [PublicApiController::class, 'getFilterOptions'])->name('api.public.filters');

// Get product statistics
Route::get('/stats', [PublicApiController::class, 'getProductStats'])->name('api.public.stats');
