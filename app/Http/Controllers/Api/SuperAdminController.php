<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\SystemActivityLog;
use App\Models\Notification;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class SuperAdminController extends Controller
{
    /**
     * Get Super Admin dashboard overview statistics
     */
    public function dashboard(Request $request)
    {
        $user = $request->user();
        $tenantId = $user->tenant_id;

        // Get counts by role
        $totalUsers = User::where('tenant_id', $tenantId)
            ->where('role', 'user')
            ->where('account_status', '!=', 'deleted')
            ->count();

        $totalAdmins = User::where('tenant_id', $tenantId)
            ->where('role', 'admin')
            ->where('account_status', '!=', 'deleted')
            ->count();

        $totalPharmacists = User::where('tenant_id', $tenantId)
            ->where('role', 'pharmacist')
            ->where('account_status', '!=', 'deleted')
            ->count();

        $totalSuperAdmins = User::where('tenant_id', $tenantId)
            ->where('role', 'super_admin')
            ->where('account_status', '!=', 'deleted')
            ->count();

        // Get suspended accounts
        $suspendedAccounts = User::where('tenant_id', $tenantId)
            ->where('account_status', 'suspended')
            ->count();

        // Get active sessions (users logged in within last 24 hours)
        $activeSessions = User::where('tenant_id', $tenantId)
            ->where('last_login_at', '>=', now()->subDay())
            ->where('account_status', 'active')
            ->count();

        // Get growth metrics (last 30 days vs previous 30 days)
        $currentPeriodUsers = User::where('tenant_id', $tenantId)
            ->where('created_at', '>=', now()->subDays(30))
            ->count();

        $previousPeriodUsers = User::where('tenant_id', $tenantId)
            ->whereBetween('created_at', [now()->subDays(60), now()->subDays(30)])
            ->count();

        $growthPercentage = $previousPeriodUsers > 0 
            ? round((($currentPeriodUsers - $previousPeriodUsers) / $previousPeriodUsers) * 100, 1)
            : 0;

        // Get recent activity (last 20 events)
        $recentActivity = SystemActivityLog::where('tenant_id', $tenantId)
            ->with('user:id,name,email,role')
            ->orderBy('created_at', 'desc')
            ->limit(20)
            ->get();

        // Get security alerts (critical events from last 7 days)
        $securityAlerts = SystemActivityLog::where('tenant_id', $tenantId)
            ->where('severity', 'critical')
            ->where('created_at', '>=', now()->subDays(7))
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        // Get failed login attempts (last 24 hours)
        $failedLogins = SystemActivityLog::where('tenant_id', $tenantId)
            ->where('event_type', 'failed_login')
            ->where('created_at', '>=', now()->subDay())
            ->count();

        return response()->json([
            'success' => true,
            'data' => [
                'overview' => [
                    'total_users' => $totalUsers,
                    'total_admins' => $totalAdmins,
                    'total_pharmacists' => $totalPharmacists,
                    'total_super_admins' => $totalSuperAdmins,
                    'suspended_accounts' => $suspendedAccounts,
                    'active_sessions' => $activeSessions,
                    'growth_percentage' => $growthPercentage,
                    'new_users_30_days' => $currentPeriodUsers,
                ],
                'recent_activity' => $recentActivity,
                'security_alerts' => $securityAlerts,
                'failed_logins_24h' => $failedLogins,
            ],
        ]);
    }

    /**
     * Get all users with filtering and pagination
     */
    public function getUsers(Request $request)
    {
        $user = $request->user();
        $tenantId = $user->tenant_id;

        $query = User::where('tenant_id', $tenantId)
            ->where('account_status', '!=', 'deleted');

        // Apply filters
        if ($request->has('role') && $request->role !== 'all') {
            $query->where('role', $request->role);
        }

        if ($request->has('status') && $request->status !== 'all') {
            $query->where('account_status', $request->status);
        }

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Apply sorting
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        // Paginate
        $perPage = $request->get('per_page', 25);
        $users = $query->with(['creator:id,name', 'updater:id,name'])
            ->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $users,
        ]);
    }

    /**
     * Get user details
     */
    public function getUserDetails(Request $request, $id)
    {
        $user = $request->user();
        $targetUser = User::where('tenant_id', $user->tenant_id)
            ->with(['creator:id,name', 'updater:id,name', 'pushTokens'])
            ->findOrFail($id);

        // Get user's activity logs
        $activityLogs = SystemActivityLog::where('user_id', $id)
            ->orderBy('created_at', 'desc')
            ->limit(50)
            ->get();

        // Get audit logs where this user was the target
        $auditLogs = AuditLog::where('tenant_id', $user->tenant_id)
            ->where('model_type', 'App\\Models\\User')
            ->where('model_id', $id)
            ->with('user:id,name,email')
            ->orderBy('created_at', 'desc')
            ->limit(50)
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'user' => $targetUser,
                'activity_logs' => $activityLogs,
                'audit_logs' => $auditLogs,
            ],
        ]);
    }

    /**
     * Create a new Admin account
     */
    public function createAdmin(Request $request)
    {
        $user = $request->user();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8',
            'phone' => 'nullable|string|max:20',
        ]);

        DB::beginTransaction();
        try {
            $admin = User::create([
                'tenant_id' => $user->tenant_id,
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
                'role' => 'admin',
                'account_status' => 'active',
                'phone' => $validated['phone'] ?? null,
                'created_by' => $user->id,
            ]);

            // Log activity
            SystemActivityLog::logActivity(
                $user->tenant_id,
                $user->id,
                'admin_created',
                'user_management',
                "Super Admin {$user->name} created new Admin account: {$admin->email}",
                $request->ip(),
                $request->userAgent(),
                ['admin_id' => $admin->id, 'admin_email' => $admin->email],
                'info'
            );

            // Notify all super admins
            Notification::notifySuperAdmins(
                $user->tenant_id,
                'admin_created',
                'New Admin Created',
                "Admin account {$admin->email} was created by {$user->name}",
                'info',
                ['admin_id' => $admin->id]
            );

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Admin account created successfully',
                'data' => $admin,
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to create admin account',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Create a new Pharmacist account
     */
    public function createPharmacist(Request $request)
    {
        $user = $request->user();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8',
            'phone' => 'nullable|string|max:20',
        ]);

        DB::beginTransaction();
        try {
            $pharmacist = User::create([
                'tenant_id' => $user->tenant_id,
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
                'role' => 'pharmacist',
                'account_status' => 'active',
                'phone' => $validated['phone'] ?? null,
                'created_by' => $user->id,
            ]);

            // Log activity
            SystemActivityLog::logActivity(
                $user->tenant_id,
                $user->id,
                'pharmacist_created',
                'user_management',
                "Super Admin {$user->name} created new Pharmacist account: {$pharmacist->email}",
                $request->ip(),
                $request->userAgent(),
                ['pharmacist_id' => $pharmacist->id, 'pharmacist_email' => $pharmacist->email],
                'info'
            );

            // Notify all super admins
            Notification::notifySuperAdmins(
                $user->tenant_id,
                'pharmacist_created',
                'New Pharmacist Created',
                "Pharmacist account {$pharmacist->email} was created by {$user->name}",
                'info',
                ['pharmacist_id' => $pharmacist->id]
            );

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Pharmacist account created successfully',
                'data' => $pharmacist,
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to create pharmacist account',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update user account
     */
    public function updateUser(Request $request, $id)
    {
        $user = $request->user();
        $targetUser = User::where('tenant_id', $user->tenant_id)->findOrFail($id);

        // Prevent modifying super admins (unless updating self)
        if ($targetUser->isSuperAdmin() && $targetUser->id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot modify other Super Admin accounts',
            ], 403);
        }

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'email' => ['sometimes', 'email', Rule::unique('users')->ignore($id)],
            'phone' => 'nullable|string|max:20',
            'role' => ['sometimes', Rule::in(['user', 'pharmacist', 'admin'])],
        ]);

        $oldValues = $targetUser->toArray();

        $targetUser->update(array_merge($validated, ['updated_by' => $user->id]));

        // Log activity
        SystemActivityLog::logActivity(
            $user->tenant_id,
            $user->id,
            'user_updated',
            'user_management',
            "Super Admin {$user->name} updated user: {$targetUser->email}",
            $request->ip(),
            $request->userAgent(),
            ['user_id' => $targetUser->id, 'changes' => $validated],
            'info'
        );

        return response()->json([
            'success' => true,
            'message' => 'User updated successfully',
            'data' => $targetUser->fresh(),
        ]);
    }

    /**
     * Suspend user account
     */
    public function suspendUser(Request $request, $id)
    {
        $user = $request->user();
        $targetUser = User::where('tenant_id', $user->tenant_id)->findOrFail($id);

        // Prevent suspending super admins
        if ($targetUser->isSuperAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot suspend Super Admin accounts',
            ], 403);
        }

        // Prevent suspending self
        if ($targetUser->id === $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot suspend your own account',
            ], 403);
        }

        $targetUser->update([
            'account_status' => 'suspended',
            'updated_by' => $user->id,
        ]);

        // Revoke all tokens
        $targetUser->tokens()->delete();

        // Log activity
        SystemActivityLog::logActivity(
            $user->tenant_id,
            $user->id,
            'user_suspended',
            'user_management',
            "Super Admin {$user->name} suspended user: {$targetUser->email}",
            $request->ip(),
            $request->userAgent(),
            ['user_id' => $targetUser->id, 'user_role' => $targetUser->role],
            'warning'
        );

        // Notify all super admins
        Notification::notifySuperAdmins(
            $user->tenant_id,
            'user_suspended',
            'User Account Suspended',
            "{$targetUser->role} account {$targetUser->email} was suspended by {$user->name}",
            'warning',
            ['user_id' => $targetUser->id]
        );

        return response()->json([
            'success' => true,
            'message' => 'User suspended successfully',
            'data' => $targetUser->fresh(),
        ]);
    }

    /**
     * Reactivate user account
     */
    public function reactivateUser(Request $request, $id)
    {
        $user = $request->user();
        $targetUser = User::where('tenant_id', $user->tenant_id)->findOrFail($id);

        $targetUser->update([
            'account_status' => 'active',
            'updated_by' => $user->id,
        ]);

        // Log activity
        SystemActivityLog::logActivity(
            $user->tenant_id,
            $user->id,
            'user_reactivated',
            'user_management',
            "Super Admin {$user->name} reactivated user: {$targetUser->email}",
            $request->ip(),
            $request->userAgent(),
            ['user_id' => $targetUser->id, 'user_role' => $targetUser->role],
            'info'
        );

        return response()->json([
            'success' => true,
            'message' => 'User reactivated successfully',
            'data' => $targetUser->fresh(),
        ]);
    }

    /**
     * Delete user account (soft delete)
     */
    public function deleteUser(Request $request, $id)
    {
        $user = $request->user();
        $targetUser = User::where('tenant_id', $user->tenant_id)->findOrFail($id);

        // Prevent deleting super admins
        if ($targetUser->isSuperAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete Super Admin accounts',
            ], 403);
        }

        // Prevent deleting self
        if ($targetUser->id === $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete your own account',
            ], 403);
        }

        $targetUser->update([
            'account_status' => 'deleted',
            'updated_by' => $user->id,
        ]);

        $targetUser->delete(); // Soft delete

        // Revoke all tokens
        $targetUser->tokens()->delete();

        // Log activity
        SystemActivityLog::logActivity(
            $user->tenant_id,
            $user->id,
            'user_deleted',
            'user_management',
            "Super Admin {$user->name} deleted user: {$targetUser->email}",
            $request->ip(),
            $request->userAgent(),
            ['user_id' => $targetUser->id, 'user_role' => $targetUser->role],
            'critical'
        );

        // Notify all super admins
        Notification::notifySuperAdmins(
            $user->tenant_id,
            'user_deleted',
            'User Account Deleted',
            "{$targetUser->role} account {$targetUser->email} was deleted by {$user->name}",
            'critical',
            ['user_id' => $targetUser->id]
        );

        return response()->json([
            'success' => true,
            'message' => 'User deleted successfully',
        ]);
    }

    /**
     * Reset user password
     */
    public function resetPassword(Request $request, $id)
    {
        $user = $request->user();
        $targetUser = User::where('tenant_id', $user->tenant_id)->findOrFail($id);

        $validated = $request->validate([
            'new_password' => 'required|string|min:8',
        ]);

        // Prevent resetting super admin passwords (unless self)
        if ($targetUser->isSuperAdmin() && $targetUser->id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot reset other Super Admin passwords',
            ], 403);
        }

        $targetUser->update([
            'password' => Hash::make($validated['new_password']),
            'updated_by' => $user->id,
        ]);

        // Revoke all tokens to force re-login
        $targetUser->tokens()->delete();

        // Log activity
        SystemActivityLog::logActivity(
            $user->tenant_id,
            $user->id,
            'password_reset',
            'security',
            "Super Admin {$user->name} reset password for user: {$targetUser->email}",
            $request->ip(),
            $request->userAgent(),
            ['user_id' => $targetUser->id],
            'warning'
        );

        return response()->json([
            'success' => true,
            'message' => 'Password reset successfully',
        ]);
    }

    /**
     * Get activity feed
     */
    public function getActivityFeed(Request $request)
    {
        $user = $request->user();
        
        $query = SystemActivityLog::where('tenant_id', $user->tenant_id)
            ->with('user:id,name,email,role');

        // Apply filters
        if ($request->has('event_type')) {
            $query->where('event_type', $request->event_type);
        }

        if ($request->has('event_category')) {
            $query->where('event_category', $request->event_category);
        }

        if ($request->has('severity')) {
            $query->where('severity', $request->severity);
        }

        if ($request->has('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        if ($request->has('date_from')) {
            $query->where('created_at', '>=', $request->date_from);
        }

        if ($request->has('date_to')) {
            $query->where('created_at', '<=', $request->date_to);
        }

        $perPage = $request->get('per_page', 50);
        $activities = $query->orderBy('created_at', 'desc')->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $activities,
        ]);
    }

    /**
     * Get security center data
     */
    public function getSecurityCenter(Request $request)
    {
        $user = $request->user();
        $tenantId = $user->tenant_id;

        // Failed login attempts (last 7 days)
        $failedLogins = SystemActivityLog::where('tenant_id', $tenantId)
            ->where('event_type', 'failed_login')
            ->where('created_at', '>=', now()->subDays(7))
            ->select('ip_address', DB::raw('count(*) as attempts'), DB::raw('MAX(created_at) as last_attempt'))
            ->groupBy('ip_address')
            ->orderBy('attempts', 'desc')
            ->limit(20)
            ->get();

        // Suspicious activity
        $suspiciousActivity = SystemActivityLog::where('tenant_id', $tenantId)
            ->where('severity', 'critical')
            ->where('created_at', '>=', now()->subDays(7))
            ->with('user:id,name,email')
            ->orderBy('created_at', 'desc')
            ->limit(20)
            ->get();

        // Recent password resets
        $passwordResets = SystemActivityLog::where('tenant_id', $tenantId)
            ->where('event_type', 'password_reset')
            ->where('created_at', '>=', now()->subDays(7))
            ->with('user:id,name,email')
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        // Users with multiple concurrent sessions
        $multipleSessionUsers = User::where('tenant_id', $tenantId)
            ->where('last_login_at', '>=', now()->subDay())
            ->withCount('tokens')
            ->having('tokens_count', '>', 1)
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'failed_logins' => $failedLogins,
                'suspicious_activity' => $suspiciousActivity,
                'password_resets' => $passwordResets,
                'multiple_session_users' => $multipleSessionUsers,
            ],
        ]);
    }

    /**
     * Get notifications
     */
    public function getNotifications(Request $request)
    {
        $user = $request->user();
        
        $query = Notification::where('user_id', $user->id);

        if ($request->has('unread_only') && $request->unread_only) {
            $query->whereNull('read_at');
        }

        $perPage = $request->get('per_page', 20);
        $notifications = $query->orderBy('created_at', 'desc')->paginate($perPage);

        $unreadCount = Notification::where('user_id', $user->id)
            ->whereNull('read_at')
            ->count();

        return response()->json([
            'success' => true,
            'data' => [
                'notifications' => $notifications,
                'unread_count' => $unreadCount,
            ],
        ]);
    }

    /**
     * Mark notification as read
     */
    public function markNotificationRead(Request $request, $id)
    {
        $user = $request->user();
        $notification = Notification::where('user_id', $user->id)->findOrFail($id);
        
        $notification->markAsRead();

        return response()->json([
            'success' => true,
            'message' => 'Notification marked as read',
        ]);
    }

    /**
     * Mark all notifications as read
     */
    public function markAllNotificationsRead(Request $request)
    {
        $user = $request->user();
        
        Notification::where('user_id', $user->id)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return response()->json([
            'success' => true,
            'message' => 'All notifications marked as read',
        ]);
    }

    /**
     * Bulk suspend users
     */
    public function bulkSuspend(Request $request)
    {
        $user = $request->user();
        
        $validated = $request->validate([
            'user_ids' => 'required|array',
            'user_ids.*' => 'exists:users,id',
        ]);

        $targetUsers = User::where('tenant_id', $user->tenant_id)
            ->whereIn('id', $validated['user_ids'])
            ->where('role', '!=', 'super_admin')
            ->where('id', '!=', $user->id)
            ->get();

        $successCount = 0;
        foreach ($targetUsers as $targetUser) {
            $targetUser->update([
                'account_status' => 'suspended',
                'updated_by' => $user->id,
            ]);
            $targetUser->tokens()->delete();
            $successCount++;
        }

        // Log activity
        SystemActivityLog::logActivity(
            $user->tenant_id,
            $user->id,
            'bulk_suspend',
            'user_management',
            "Super Admin {$user->name} suspended {$successCount} users",
            $request->ip(),
            $request->userAgent(),
            ['user_ids' => $validated['user_ids'], 'success_count' => $successCount],
            'warning'
        );

        return response()->json([
            'success' => true,
            'message' => "{$successCount} users suspended successfully",
            'data' => ['success_count' => $successCount],
        ]);
    }

    /**
     * Bulk reactivate users
     */
    public function bulkReactivate(Request $request)
    {
        $user = $request->user();
        
        $validated = $request->validate([
            'user_ids' => 'required|array',
            'user_ids.*' => 'exists:users,id',
        ]);

        $targetUsers = User::where('tenant_id', $user->tenant_id)
            ->whereIn('id', $validated['user_ids'])
            ->get();

        $successCount = 0;
        foreach ($targetUsers as $targetUser) {
            $targetUser->update([
                'account_status' => 'active',
                'updated_by' => $user->id,
            ]);
            $successCount++;
        }

        // Log activity
        SystemActivityLog::logActivity(
            $user->tenant_id,
            $user->id,
            'bulk_reactivate',
            'user_management',
            "Super Admin {$user->name} reactivated {$successCount} users",
            $request->ip(),
            $request->userAgent(),
            ['user_ids' => $validated['user_ids'], 'success_count' => $successCount],
            'info'
        );

        return response()->json([
            'success' => true,
            'message' => "{$successCount} users reactivated successfully",
            'data' => ['success_count' => $successCount],
        ]);
    }

    /**
     * Bulk delete users
     */
    public function bulkDelete(Request $request)
    {
        $user = $request->user();
        
        $validated = $request->validate([
            'user_ids' => 'required|array',
            'user_ids.*' => 'exists:users,id',
        ]);

        $targetUsers = User::where('tenant_id', $user->tenant_id)
            ->whereIn('id', $validated['user_ids'])
            ->where('role', '!=', 'super_admin')
            ->where('id', '!=', $user->id)
            ->get();

        $successCount = 0;
        foreach ($targetUsers as $targetUser) {
            $targetUser->update([
                'account_status' => 'deleted',
                'updated_by' => $user->id,
            ]);
            $targetUser->delete();
            $targetUser->tokens()->delete();
            $successCount++;
        }

        // Log activity
        SystemActivityLog::logActivity(
            $user->tenant_id,
            $user->id,
            'bulk_delete',
            'user_management',
            "Super Admin {$user->name} deleted {$successCount} users",
            $request->ip(),
            $request->userAgent(),
            ['user_ids' => $validated['user_ids'], 'success_count' => $successCount],
            'critical'
        );

        return response()->json([
            'success' => true,
            'message' => "{$successCount} users deleted successfully",
            'data' => ['success_count' => $successCount],
        ]);
    }
}
