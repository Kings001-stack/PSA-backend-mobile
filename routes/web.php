<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\DashboardController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect('/dashboard');
});

// Auth routes
Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'create'])->name('login');
    Route::post('/login', [LoginController::class, 'store']);
});

Route::post('/logout', [LoginController::class, 'destroy'])->name('logout');

// Protected routes - Pharmacist/Admin only
Route::middleware(['auth', 'web.pharmacist'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    
    // Admin routes prefix
    Route::prefix('admin')->name('admin.')->group(function () {
        // Inventory Management
        Route::prefix('inventory')->name('inventory.')->group(function () {
            Route::get('/', [\App\Http\Controllers\Admin\InventoryController::class, 'index'])->name('index');
            Route::post('/', [\App\Http\Controllers\Admin\InventoryController::class, 'store'])->name('store');
            Route::put('/{inventory}', [\App\Http\Controllers\Admin\InventoryController::class, 'update'])->name('update');
            Route::delete('/{inventory}', [\App\Http\Controllers\Admin\InventoryController::class, 'destroy'])->name('destroy');
            Route::get('/import', [\App\Http\Controllers\Admin\ImportController::class, 'index'])->name('import');
            Route::post('/import', [\App\Http\Controllers\Admin\ImportController::class, 'store'])->name('import.store');
        });

        // Refill Management
        Route::prefix('refills')->name('refills.')->group(function () {
            Route::get('/', [\App\Http\Controllers\Admin\RefillController::class, 'index'])->name('index');
            Route::put('/{refill}/approve', [\App\Http\Controllers\Admin\RefillController::class, 'approve'])->name('approve');
            Route::put('/{refill}/reject', [\App\Http\Controllers\Admin\RefillController::class, 'reject'])->name('reject');
            Route::put('/{refill}/ready', [\App\Http\Controllers\Admin\RefillController::class, 'markReady'])->name('ready');
            Route::put('/{refill}/complete', [\App\Http\Controllers\Admin\RefillController::class, 'complete'])->name('complete');
        });

        // User Management
        Route::prefix('users')->name('users.')->group(function () {
            Route::get('/', [\App\Http\Controllers\Admin\UserController::class, 'index'])->name('index');
            Route::get('/{user}', [\App\Http\Controllers\Admin\UserController::class, 'show'])->name('show');
            Route::post('/', [\App\Http\Controllers\Admin\UserController::class, 'store'])->name('store');
            Route::put('/{user}', [\App\Http\Controllers\Admin\UserController::class, 'update'])->name('update');
            Route::put('/{user}/suspend', [\App\Http\Controllers\Admin\UserController::class, 'suspend'])->name('suspend');
            Route::put('/{user}/activate', [\App\Http\Controllers\Admin\UserController::class, 'activate'])->name('activate');
            Route::post('/{user}/notify', [\App\Http\Controllers\Admin\UserController::class, 'sendNotification'])->name('notify');
        });

        // Advertisement Management
        Route::prefix('adverts')->name('adverts.')->group(function () {
            Route::get('/', [\App\Http\Controllers\Admin\AdvertController::class, 'index'])->name('index');
            Route::post('/', [\App\Http\Controllers\Admin\AdvertController::class, 'store'])->name('store');
            Route::put('/{advert}', [\App\Http\Controllers\Admin\AdvertController::class, 'update'])->name('update');
            Route::delete('/{advert}', [\App\Http\Controllers\Admin\AdvertController::class, 'destroy'])->name('destroy');
            Route::put('/{advert}/toggle', [\App\Http\Controllers\Admin\AdvertController::class, 'toggle'])->name('toggle');
        });

        // Analytics
        Route::get('/analytics', [\App\Http\Controllers\Admin\AnalyticsController::class, 'index'])->name('analytics.index');

        // Low Stock Alerts
        Route::prefix('alerts')->name('alerts.')->group(function () {
            Route::get('/', [\App\Http\Controllers\Admin\AlertController::class, 'index'])->name('index');
            Route::put('/{alert}/resolve', [\App\Http\Controllers\Admin\AlertController::class, 'resolve'])->name('resolve');
        });

        // Activity Logs
        Route::prefix('activity')->name('activity.')->group(function () {
            Route::get('/', [\App\Http\Controllers\Admin\ActivityLogController::class, 'index'])->name('index');
            Route::get('/export', [\App\Http\Controllers\Admin\ActivityLogController::class, 'export'])->name('export');
        });
    });
});

// Super Admin routes
Route::middleware(['auth', 'web.super.admin'])->prefix('super-admin')->name('super-admin.')->group(function () {
    Route::get('/dashboard', [\App\Http\Controllers\SuperAdmin\DashboardController::class, 'index'])->name('dashboard');
    // Add more super admin routes here
});
