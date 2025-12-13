<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ColorController;
use App\Http\Controllers\DesignController;
use App\Http\Controllers\MaterialController;
use App\Http\Controllers\TransparencyController;
use App\Http\Controllers\CharacteristicController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProductMediaController;
use App\Http\Controllers\ConsultationController;
use App\Http\Controllers\ContactUsController;
use App\Http\Controllers\Api\PublicApiController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// Public API routes (no authentication required)
Route::prefix('public')->group(function () {
    // Product listing and search
    Route::get('/products', [PublicApiController::class, 'getProducts'])->name('api.public.products');

    // Get single product

    // Get filter options
    Route::get('/filters', [PublicApiController::class, 'getFilterOptions'])->name('api.public.filters');

    // Get featured products (must come before /products/{id})
    Route::get('/products/featured', [PublicApiController::class, 'getFeaturedProducts'])->name('api.public.featured');

    // Get latest products from the last one year (must come before /products/{id})
    Route::get('/products/latest', [PublicApiController::class, 'getLatestProducts'])->name('api.public.latest');

    Route::get('/products/{id}', [PublicApiController::class, 'getProduct'])->name('api.public.product');
    // Get product statistics
    Route::get('/stats', [PublicApiController::class, 'getProductStats'])->name('api.public.stats');

    // Consultation submission (public endpoint)
    Route::post('/consultations', [ConsultationController::class, 'store'])->name('api.public.consultations.store');

    // Contact us submission (public endpoint)
    Route::post('/contact-us', [ContactUsController::class, 'store'])->name('api.public.contact-us.store');
});

// All API routes require authentication
Route::middleware('auth:sanctum')->group(function () {
    // Colors API Routes
    Route::apiResource('colors', ColorController::class);

    // Designs API Routes
    Route::apiResource('designs', DesignController::class);

    // Materials API Routes
    Route::apiResource('materials', MaterialController::class);

    // Transparency API Routes
    Route::apiResource('transparency', TransparencyController::class);

    // Characteristics API Routes
    Route::apiResource('characteristics', CharacteristicController::class);

    // Products API Routes
    Route::get('products/related-data', [ProductController::class, 'getRelatedData']);
    Route::apiResource('products', ProductController::class);

    // Product Media API Routes
    Route::apiResource('product-media', ProductMediaController::class);
    Route::get('products/{product}/media', [ProductMediaController::class, 'getProductMedia']);

    // Consultations API Routes (admin only)
    Route::apiResource('consultations', ConsultationController::class);
    Route::patch('consultations/{consultation}/status', [ConsultationController::class, 'updateStatus'])->name('consultations.update-status');
    Route::get('consultations/status/{status}', [ConsultationController::class, 'getByStatus'])->name('consultations.by-status');

    // Contact Us API Routes (admin only)
    Route::apiResource('contact-us', ContactUsController::class);
    Route::patch('contact-us/{contactUs}/status', [ContactUsController::class, 'updateStatus'])->name('contact-us.update-status');
    Route::get('contact-us/status/{status}', [ContactUsController::class, 'getByStatus'])->name('contact-us.by-status');
    Route::get('contact-us/project-type/{projectType}', [ContactUsController::class, 'getByProjectType'])->name('contact-us.by-project-type');
});
