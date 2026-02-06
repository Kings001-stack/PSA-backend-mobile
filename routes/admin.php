<?php

use App\Http\Controllers\Admin\AdminAuthController;
use App\Http\Controllers\Admin\ConversationController;
use App\Http\Controllers\Admin\UploadController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Admin API Routes
|--------------------------------------------------------------------------
*/

// Public auth routes
Route::post('/login', [AdminAuthController::class, 'login']);

// Protected admin routes
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AdminAuthController::class, 'logout']);
    Route::get('/me', [AdminAuthController::class, 'me']);

    // Conversations
    Route::get('/conversations', [ConversationController::class, 'index']);
    Route::get('/conversations/{id}', [ConversationController::class, 'show']);
    Route::post('/conversations/{id}/assign', [ConversationController::class, 'assign']);
    Route::post('/conversations/{id}/respond', [ConversationController::class, 'respond']);
    Route::post('/conversations/{id}/resolve', [ConversationController::class, 'resolve']);

    // Uploads
    Route::post('/upload/medications', [UploadController::class, 'uploadMedications']);
    Route::post('/upload/inventory', [UploadController::class, 'uploadInventory']);
    Route::post('/upload/faq', [UploadController::class, 'uploadFaq']);
    Route::get('/upload/status', [UploadController::class, 'status']);
    Route::get('/upload/template/{type}', [UploadController::class, 'downloadTemplate']);
});


