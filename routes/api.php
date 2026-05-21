<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ChatController;
use App\Http\Controllers\Api\InventoryController;
use App\Http\Controllers\Api\InventoryImportController;
use App\Http\Controllers\Api\MedicationController;
use App\Http\Controllers\Api\MedicationSearchController;
use App\Http\Controllers\Api\AdminController;
use App\Http\Controllers\Api\AdvertController;
use App\Http\Controllers\Api\RefillController;
use App\Http\Controllers\Api\PrescriptionController;
use App\Http\Controllers\Api\SuperAdminController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Secure API endpoints for the Mobile Pharmacy App.
| Role-based access control enforced via middleware.
|
*/

// Public Routes
Route::get('/health', fn() => response()->json(['status' => 'ok', 'message' => 'API is working!']));
Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);

// Debug endpoint for refill status (remove in production)
Route::get('/debug/refills', function () {
    $refills = \App\Models\RefillRequest::with(['user:id,name', 'medication:id,name'])
        ->orderBy('created_at', 'desc')
        ->limit(10)
        ->get()
        ->map(function($refill) {
            return [
                'id' => $refill->id,
                'user' => $refill->user?->name,
                'medication' => $refill->medication?->name,
                'status' => $refill->status,
                'viewed_at' => $refill->viewed_at,
                'user_viewed_at' => $refill->user_viewed_at,
                'reviewed_at' => $refill->reviewed_at,
                'created_at' => $refill->created_at,
            ];
        });
    
    return response()->json([
        'refills' => $refills,
        'stats' => [
            'total' => \App\Models\RefillRequest::count(),
            'pending' => \App\Models\RefillRequest::where('status', 'pending')->count(),
            'approved' => \App\Models\RefillRequest::where('status', 'approved')->count(),
            'rejected' => \App\Models\RefillRequest::where('status', 'rejected')->count(),
        ],
    ]);
});

// Protected Routes (Authenticated Users)
Route::middleware(['auth:sanctum', 'api.rate.limit:60'])->group(function () {
    
    // ========================================
    // AUTH & USER PROFILE
    // ========================================
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', [AuthController::class, 'user']);
    Route::match(['patch', 'post'], '/user/profile', [AuthController::class, 'updateProfile']);

    // ========================================
    // PUSH NOTIFICATIONS
    // ========================================
    Route::prefix('user')->group(function () {
        Route::post('/push-token', [\App\Http\Controllers\Api\PushTokenController::class, 'store']);
        Route::delete('/push-token', [\App\Http\Controllers\Api\PushTokenController::class, 'destroy']);
        Route::get('/push-tokens', [\App\Http\Controllers\Api\PushTokenController::class, 'index']);
        Route::post('/push-token/test', [\App\Http\Controllers\Api\PushTokenController::class, 'sendTest']);
        
        // Notification Preferences
        Route::get('/notification-preferences', [\App\Http\Controllers\Api\UserController::class, 'getNotificationPreferences']);
        Route::post('/notification-preferences', [\App\Http\Controllers\Api\UserController::class, 'updateNotificationPreferences']);
    });

    // ========================================
    // CHAT (AI Assistant - No Medical Decisions)
    // ========================================
    Route::prefix('chat')->group(function () {
        Route::post('/send', [ChatController::class, 'send']);
        Route::get('/history', [ChatController::class, 'history']);
        Route::post('/clear', [ChatController::class, 'clear']);
    });

    // ========================================
    // MEDICATIONS & INVENTORY (Read-Only for Users)
    // ========================================
    Route::get('/inventory', [InventoryController::class, 'index']);
    Route::get('/medications', [MedicationController::class, 'index']);
    Route::get('/medications/search', [MedicationSearchController::class, 'search']);
    
    // ========================================
    // ADVERTS (Active Only for Users)
    // ========================================
    Route::get('/adverts', [AdvertController::class, 'index']);
    
    // ========================================
    // USER MODULE: PRESCRIPTIONS
    // ========================================
    Route::prefix('prescriptions')->group(function () {
        Route::get('/', [PrescriptionController::class, 'index']); // User's own prescriptions
        Route::post('/upload', [PrescriptionController::class, 'upload']); // Upload prescription document
        Route::get('/{id}', [PrescriptionController::class, 'show']); // View prescription details
        Route::get('/{id}/download', [PrescriptionController::class, 'download']); // Download document
    });
    
    // ========================================
    // USER MODULE: REFILL REQUESTS
    // ========================================
    Route::prefix('refills')->group(function () {
        Route::get('/', [RefillController::class, 'index']); // User's refill requests
        Route::get('/prescriptions', [RefillController::class, 'prescriptions']); // Active prescriptions for refill
        Route::get('/medications', [RefillController::class, 'medications']); // Available medications
        Route::post('/', [RefillController::class, 'store']); // Submit refill request
        Route::patch('/{id}/cancel', [RefillController::class, 'cancel']); // Cancel pending request
        Route::post('/{id}/viewed', [RefillController::class, 'markAsViewed']); // Mark refill as viewed by user
    });
    
    // ========================================
    // PHARMACIST MODULE: REFILL MANAGEMENT
    // ========================================
    Route::middleware('pharmacist')->prefix('pharmacist')->group(function () {
        
        // Refill Queue Management
        Route::get('/refills', [RefillController::class, 'pharmacistIndex']); // View all refill requests
        Route::get('/refills/{id}', [RefillController::class, 'show']); // View refill details
        Route::get('/refills/{id}/audit', [RefillController::class, 'auditLog']); // View audit log
        
        // Refill Actions (PHARMACIST DECISION AUTHORITY)
        Route::post('/refills/{id}/approve', [RefillController::class, 'approve']); // Approve refill
        Route::post('/refills/{id}/reject', [RefillController::class, 'reject']); // Reject refill (reason required)
        Route::post('/refills/{id}/ready', [RefillController::class, 'markReady']); // Mark ready for pickup
        Route::post('/refills/{id}/collected', [RefillController::class, 'markCollected']); // Mark as collected
        
        // Prescription Verification
        Route::get('/prescriptions', [PrescriptionController::class, 'pharmacistIndex']); // View all prescriptions
        Route::post('/prescriptions/{id}/verify', [PrescriptionController::class, 'verify']); // Verify prescription
        Route::get('/prescriptions/{id}/download', [PrescriptionController::class, 'download']); // Download document
    });
    
    // ========================================
    // ADMIN MODULE: SYSTEM MANAGEMENT
    // ========================================
    Route::middleware('admin')->prefix('admin')->group(function () {
        
        // Dashboard & Analytics
        Route::get('/dashboard', [AdminController::class, 'dashboard']);
        Route::get('/analytics', [\App\Http\Controllers\Api\AnalyticsController::class, 'index']);
        
        // Low Stock Alerts
        Route::prefix('alerts')->group(function () {
            Route::get('/', [\App\Http\Controllers\Api\AlertController::class, 'index']);
            Route::get('/statistics', [\App\Http\Controllers\Api\AlertController::class, 'statistics']);
            Route::post('/{id}/resolve', [\App\Http\Controllers\Api\AlertController::class, 'resolve']);
            Route::post('/check', [\App\Http\Controllers\Api\AlertController::class, 'checkAlerts']);
        });
        
        // Inventory Management
        Route::post('/inventory', [InventoryController::class, 'store']);
        Route::match(['put', 'patch'], '/inventory/{id}', [InventoryController::class, 'update']);
        Route::delete('/inventory/{id}', [InventoryController::class, 'destroy']);
        Route::post('/inventory/import', [InventoryImportController::class, 'import']);
        
        // Advert Management
        Route::get('/adverts', [AdvertController::class, 'adminIndex']);
        Route::post('/adverts', [AdvertController::class, 'store']);
        Route::match(['put', 'patch'], '/adverts/{id}', [AdvertController::class, 'update']);
        Route::delete('/adverts/{id}', [AdvertController::class, 'destroy']);
        
        // User & Role Management
        Route::get('/users', [\App\Http\Controllers\Api\UserController::class, 'index']);
        Route::get('/users/search', [\App\Http\Controllers\Api\UserController::class, 'search']);
        Route::post('/users', [\App\Http\Controllers\Api\UserController::class, 'store']); // Create pharmacist accounts
        Route::patch('/users/{id}/role', [\App\Http\Controllers\Api\UserController::class, 'updateRole']); // Change user role
        Route::delete('/users/{id}', [\App\Http\Controllers\Api\UserController::class, 'destroy']); // Remove user
        
        // System Logs & Audit
        Route::get('/audit-logs', [AdminController::class, 'auditLogs']);
        Route::get('/refills/audit', [AdminController::class, 'refillAuditLogs']);
    });

    // ========================================
    // SUPER ADMIN MODULE: PLATFORM OVERSIGHT
    // ========================================
    Route::middleware('super.admin')->prefix('super-admin')->group(function () {
        
        // Dashboard & Overview
        Route::get('/dashboard', [SuperAdminController::class, 'dashboard']);
        
        // User Management
        Route::get('/users', [SuperAdminController::class, 'getUsers']);
        Route::get('/users/{id}', [SuperAdminController::class, 'getUserDetails']);
        Route::put('/users/{id}', [SuperAdminController::class, 'updateUser']);
        Route::post('/users/{id}/suspend', [SuperAdminController::class, 'suspendUser']);
        Route::post('/users/{id}/reactivate', [SuperAdminController::class, 'reactivateUser']);
        Route::delete('/users/{id}', [SuperAdminController::class, 'deleteUser']);
        Route::post('/users/{id}/reset-password', [SuperAdminController::class, 'resetPassword']);
        
        // Admin Management
        Route::post('/admins', [SuperAdminController::class, 'createAdmin']);
        
        // Pharmacist Management
        Route::post('/pharmacists', [SuperAdminController::class, 'createPharmacist']);
        
        // Bulk Operations
        Route::post('/users/bulk-suspend', [SuperAdminController::class, 'bulkSuspend']);
        Route::post('/users/bulk-reactivate', [SuperAdminController::class, 'bulkReactivate']);
        Route::post('/users/bulk-delete', [SuperAdminController::class, 'bulkDelete']);
        
        // Activity Feed & Logs
        Route::get('/activity-feed', [SuperAdminController::class, 'getActivityFeed']);
        
        // Security Center
        Route::get('/security-center', [SuperAdminController::class, 'getSecurityCenter']);
        
        // Notifications
        Route::get('/notifications', [SuperAdminController::class, 'getNotifications']);
        Route::post('/notifications/{id}/read', [SuperAdminController::class, 'markNotificationRead']);
        Route::post('/notifications/read-all', [SuperAdminController::class, 'markAllNotificationsRead']);
    });
});
