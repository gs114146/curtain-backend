<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\{
    HomeController,
    DashboardController,
    FuelInjectProgramController,
    ImportFileController,
    ColorController,
    DesignController,
    MaterialController,
    TransparencyController,
    CharacteristicController,
    ProductController,
    ProductMediaController,
    UsageController,
    ConsultationController,
    ContactUsController
};

// Auth::routes();
Auth::routes(['register' => false]);


// Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');

Route::middleware(['auth'])->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    // Profile Routes
    Route::prefix('profile')->group(function () {
        Route::get('/settings', [HomeController::class, 'profileSettings'])->name('profile.settings');
        Route::put('/update', [HomeController::class, 'updateProfile'])->name('profile.update');
        Route::put('/change-password', [HomeController::class, 'changePassword'])->name('profile.change-password');
    });

    // Admin CRUD Routes
    Route::prefix('admin')->name('admin.')->group(function () {
        // Colors
        Route::resource('colors', ColorController::class);

        // Designs
        Route::resource('designs', DesignController::class);

        // Materials
        Route::resource('materials', MaterialController::class);

        // Transparency
        Route::resource('transparency', TransparencyController::class);

        // Characteristics
        Route::resource('characteristics', CharacteristicController::class);

        // Usage
        Route::resource('usage', UsageController::class);

        // Products
        Route::get('products/related-data', [ProductController::class, 'getRelatedData'])->name('products.related-data');
        Route::post('products/generate-sku', [ProductController::class, 'generateSku'])->name('products.generate-sku');
        Route::get('products/{product}/edit-modal', [ProductController::class, 'editModal'])->name('products.edit-modal');
        Route::resource('products', ProductController::class);
        Route::get('products/{product}/media', [ProductController::class, 'media'])->name('products.media.index');

        // Product Variants
        Route::post('products/{product}/variants', [ProductController::class, 'addVariant'])->name('products.variants.add');
        Route::put('variants/{variant}', [ProductController::class, 'updateVariant'])->name('variants.update');
        Route::delete('variants/{variant}', [ProductController::class, 'deleteVariant'])->name('variants.delete');

        // Product Media
        Route::resource('product-media', ProductMediaController::class);

        // Consultations (Appointments)
        Route::resource('consultations', ConsultationController::class);

        // Contact Us
        Route::resource('contact-us', ContactUsController::class);
    });

    // Debug/Development Routes (only accessible when authenticated)
    Route::get('routes', function () {
        $routeCollection = Route::getRoutes();
        $title = "Route List";
        return view('routes', compact('routeCollection', 'title'));
    });

    // Test route for debugging (only accessible when authenticated)
    Route::get('test-colors', function () {
        return response()->json([
            'message' => 'Colors route is working',
            'colors_count' => App\Models\Color::count(),
            'colors' => App\Models\Color::take(5)->get()
        ]);
    });
});
