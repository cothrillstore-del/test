<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\Auth\LoginController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\ProductManagementController;
use App\Http\Controllers\Admin\ReviewModerationController;
use App\Http\Controllers\Admin\UserManagementController;
use App\Http\Controllers\Admin\ArticleManagementController;
use App\Http\Controllers\Admin\BrandManagementController;
use App\Http\Controllers\Admin\CategoryManagementController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::prefix('admin')->name('admin.')->group(function () {

    // Guest (chưa login)
    Route::middleware('guest')->group(function () {
        Route::get('login', [LoginController::class, 'showLoginForm'])->name('login');
        Route::post('login', [LoginController::class, 'login'])->name('login.submit');
    });

    // Đã login
    Route::middleware(['auth'])->group(function () {   // ⟵ bỏ admin.auth ở đây
        Route::post('logout', [LoginController::class, 'logout'])->name('logout');
        Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

        // Chỉ Admin
        Route::middleware('admin.auth:admin')->group(function () {
            Route::resource('users', UserManagementController::class);
            Route::post('users/{user}/toggle-status', [UserManagementController::class, 'toggleStatus'])
                  ->name('users.toggle-status');
        });

        // Admin + Editor
        Route::middleware('admin.auth:admin,editor')->group(function () {
            Route::resource('products', ProductManagementController::class);
            Route::post('/{product}/toggle-featured', [ProductManagementController::class, 'toggleFeatured'])->name('products.toggle-featured');
            Route::post('/bulk-delete', [ProductManagementController::class, 'bulkDelete'])->name('products.bulk-delete');
            Route::resource('articles', ArticleManagementController::class);

            // Brands Management
            Route::prefix('brands')->name('brands.')->group(function () {
                Route::get('/', [BrandManagementController::class, 'index'])->name('index');
                Route::get('/create', [BrandManagementController::class, 'create'])->name('create');
                Route::post('/', [BrandManagementController::class, 'store'])->name('store');
                Route::get('/{brand}', [BrandManagementController::class, 'show'])->name('show');
                Route::get('/{brand}/edit', [BrandManagementController::class, 'edit'])->name('edit');
                Route::put('/{brand}', [BrandManagementController::class, 'update'])->name('update');
                Route::delete('/{brand}', [BrandManagementController::class, 'destroy'])->name('destroy');
                Route::post('/{brand}/toggle-featured', [BrandManagementController::class, 'toggleFeatured'])->name('toggle-featured');
                Route::post('/update-order', [BrandManagementController::class, 'updateOrder'])->name('update-order');
            });

            // Categories Management
            Route::prefix('categories')->name('categories.')->group(function () {
                Route::get('/', [CategoryManagementController::class, 'index'])->name('index');
                Route::get('/create', [CategoryManagementController::class, 'create'])->name('create');
                Route::post('/', [CategoryManagementController::class, 'store'])->name('store');
                Route::get('/tree', [CategoryManagementController::class, 'getCategoryTree'])->name('tree');
                Route::get('/{category}', [CategoryManagementController::class, 'show'])->name('show');
                Route::get('/{category}/edit', [CategoryManagementController::class, 'edit'])->name('edit');
                Route::put('/{category}', [CategoryManagementController::class, 'update'])->name('update');
                Route::delete('/{category}', [CategoryManagementController::class, 'destroy'])->name('destroy');
                Route::post('/{category}/toggle-active', [CategoryManagementController::class, 'toggleActive'])->name('toggle-active');
                Route::post('/update-order', [CategoryManagementController::class, 'updateOrder'])->name('update-order');
            });
        });

        // Tất cả user backend
        Route::resource('reviews', ReviewModerationController::class);
        Route::post('reviews/{review}/approve', [ReviewModerationController::class, 'approve'])->name('reviews.approve');
        Route::post('reviews/{review}/reject',  [ReviewModerationController::class, 'reject'])->name('reviews.reject');
    });
});

require __DIR__.'/auth.php';
