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

// Protected routes
Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    
    // Inventory & Medications
    Route::resource('medications', \App\Http\Controllers\MedicationController::class)->only(['index', 'store', 'update', 'destroy']);
    Route::resource('inventory', \App\Http\Controllers\InventoryController::class)->only(['index', 'store', 'update', 'destroy']);

    // AI Chat
    Route::get('/chat', [\App\Http\Controllers\ChatController::class, 'index'])->name('chat.index');
    Route::post('/chat/send', [\App\Http\Controllers\ChatController::class, 'send'])->name('chat.send');
    Route::get('/chat/messages', [\App\Http\Controllers\ChatController::class, 'messages'])->name('chat.messages');
});
