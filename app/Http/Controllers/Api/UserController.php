<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    /**
     * Get all users (admin only).
     */
    public function index(Request $request)
    {
        $user = $request->user();
        
        // Get all users from the same tenant
        $users = User::where('tenant_id', $user->tenant_id)
            ->orderBy('created_at', 'desc')
            ->get(['id', 'name', 'email', 'role', 'created_at']);

        // Calculate statistics
        $stats = [
            'total_users' => $users->count(),
            'admins' => $users->where('role', 'admin')->count(),
            'pharmacists' => $users->where('role', 'pharmacist')->count(),
            'users' => $users->where('role', 'user')->count(),
        ];

        return response()->json([
            'users' => $users,
            'stats' => $stats,
        ]);
    }

    /**
     * Search users by name or email.
     */
    public function search(Request $request)
    {
        $request->validate([
            'query' => 'required|string|min:1',
        ]);

        $user = $request->user();
        $query = $request->input('query');

        $users = User::where('tenant_id', $user->tenant_id)
            ->where(function ($q) use ($query) {
                $q->where('name', 'like', "%{$query}%")
                    ->orWhere('email', 'like', "%{$query}%");
            })
            ->orderBy('created_at', 'desc')
            ->get(['id', 'name', 'email', 'role', 'created_at']);

        return response()->json(['users' => $users]);
    }

    /**
     * Get user's notification preferences.
     */
    public function getNotificationPreferences(Request $request)
    {
        $user = $request->user();

        return response()->json([
            'success' => true,
            'preferences' => [
                'notify_refill_status' => $user->notify_refill_status,
                'notify_prescription_reminders' => $user->notify_prescription_reminders,
                'notify_pharmacy_updates' => $user->notify_pharmacy_updates,
            ],
        ]);
    }

    /**
     * Update user's notification preferences.
     */
    public function updateNotificationPreferences(Request $request)
    {
        $request->validate([
            'notify_refill_status' => 'sometimes|boolean',
            'notify_prescription_reminders' => 'sometimes|boolean',
            'notify_pharmacy_updates' => 'sometimes|boolean',
        ]);

        $user = $request->user();
        
        $user->update($request->only([
            'notify_refill_status',
            'notify_prescription_reminders',
            'notify_pharmacy_updates',
        ]));

        return response()->json([
            'success' => true,
            'message' => 'Notification preferences updated successfully',
            'preferences' => [
                'notify_refill_status' => $user->notify_refill_status,
                'notify_prescription_reminders' => $user->notify_prescription_reminders,
                'notify_pharmacy_updates' => $user->notify_pharmacy_updates,
            ],
        ]);
    }
}
