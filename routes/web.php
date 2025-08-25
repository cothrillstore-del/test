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
        // Dashboard routes
        Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard.index');
        Route::get('/dashboard/analytics', [DashboardController::class, 'getAnalyticsData'])->name('dashboard.analytics');
        Route::get('/dashboard/export', [DashboardController::class, 'exportReport'])->name('dashboard.export');

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

        // Reviews Management
        Route::prefix('reviews')->name('reviews.')->group(function () {
            Route::get('/', [ReviewModerationController::class, 'index'])->name('index');
            Route::get('/statistics', [ReviewModerationController::class, 'statistics'])->name('statistics');
            Route::get('/export', [ReviewModerationController::class, 'export'])->name('export');
            Route::get('/{review}', [ReviewModerationController::class, 'show'])->name('show');
            Route::get('/{review}/edit', [ReviewModerationController::class, 'edit'])->name('edit');
            Route::put('/{review}', [ReviewModerationController::class, 'update'])->name('update');
            Route::delete('/{review}', [ReviewModerationController::class, 'destroy'])->name('destroy');
            
            // Moderation actions
            Route::post('/{review}/approve', [ReviewModerationController::class, 'approve'])->name('approve');
            Route::post('/{review}/reject', [ReviewModerationController::class, 'reject'])->name('reject');
            Route::post('/{review}/flag', [ReviewModerationController::class, 'flag'])->name('flag');
            Route::post('/{review}/toggle-featured', [ReviewModerationController::class, 'toggleFeatured'])->name('toggle-featured');
            
            // Bulk actions
            Route::post('/bulk-approve', [ReviewModerationController::class, 'bulkApprove'])->name('bulk-approve');
            Route::post('/bulk-reject', [ReviewModerationController::class, 'bulkReject'])->name('bulk-reject');
        });

        // Tất cả user backend
        Route::resource('reviews', ReviewModerationController::class);
        Route::post('reviews/{review}/approve', [ReviewModerationController::class, 'approve'])->name('reviews.approve');
        Route::post('reviews/{review}/reject',  [ReviewModerationController::class, 'reject'])->name('reviews.reject');
    });
});

require __DIR__.'/auth.php';
