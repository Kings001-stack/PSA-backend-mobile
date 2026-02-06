<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ChatController;
use App\Http\Controllers\Api\InventoryController;
use App\Http\Controllers\Api\InventoryImportController;
use App\Http\Controllers\Api\MedicationController;
use App\Http\Controllers\Api\MedicationSearchController;
use App\Http\Controllers\Api\AdminController;
use App\Http\Controllers\Api\AdvertController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Secure API endpoints for the Mobile Pharmacy App.
|
*/

// Public Routes
Route::get('/health', fn() => response()->json(['status' => 'ok', 'message' => 'API is working!']));
Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);

// Protected Routes
Route::middleware(['auth:sanctum'])->group(function () {
    
    // Auth & User
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', [AuthController::class, 'user']);
    Route::patch('/user/profile', [AuthController::class, 'updateProfile']);

    // Chat
    Route::prefix('chat')->group(function () {
        Route::post('/send', [ChatController::class, 'send']);
        Route::get('/history', [ChatController::class, 'history']);
        Route::post('/clear', [ChatController::class, 'clear']);
    });

    // Inventory & Medications
    Route::get('/inventory', [InventoryController::class, 'index']);
    Route::get('/medications', [MedicationController::class, 'index']); // For admin list
    Route::get('/medications/search', [MedicationSearchController::class, 'search']); // For user search
    Route::post('/inventory/import', [InventoryImportController::class, 'import'])->middleware('can:admin');
    
    // Adverts (for users - active only)
    Route::get('/adverts', [AdvertController::class, 'index']);
    
    // Admin Only
    Route::middleware('can:admin')->prefix('admin')->group(function () {
        Route::get('/dashboard', [AdminController::class, 'dashboard']);
        
        Route::post('/inventory', [InventoryController::class, 'store']);
        Route::match(['put', 'patch'], '/inventory/{id}', [InventoryController::class, 'update']);
        Route::delete('/inventory/{id}', [InventoryController::class, 'destroy']);
        
        // Advert Management
        Route::get('/adverts', [AdvertController::class, 'adminIndex']);
        Route::post('/adverts', [AdvertController::class, 'store']);
        Route::match(['put', 'patch'], '/adverts/{id}', [AdvertController::class, 'update']);
        Route::delete('/adverts/{id}', [AdvertController::class, 'destroy']);
    });
});
