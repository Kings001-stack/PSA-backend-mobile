<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\SystemActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Storage;

class AuthController extends Controller
{
    /**
     * Handle user login.
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
            'device_name' => 'required|string',
        ]);

        $user = User::where('email', $request->email)->first();

        // Check if user exists first
        if (!$user) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        // Check account status BEFORE password validation
        if ($user->account_status === 'suspended') {
            SystemActivityLog::logActivity(
                $user->tenant_id,
                $user->id,
                'suspended_login_attempt',
                'security',
                "Suspended account login attempt: {$user->email}",
                $request->ip(),
                $request->userAgent(),
                ['user_id' => $user->id],
                'critical'
            );

            throw ValidationException::withMessages([
                'email' => ['Your account has been suspended by the administrator. Please contact the pharmacy management or support team to resolve this issue and reactivate your account.'],
            ]);
        }

        if ($user->account_status === 'deleted') {
            throw ValidationException::withMessages([
                'email' => ['This account no longer exists.'],
            ]);
        }

        // Now check password
        if (!Hash::check($request->password, $user->password)) {
            // Log failed login attempt
            SystemActivityLog::logActivity(
                $user->tenant_id,
                $user->id,
                'failed_login',
                'auth',
                "Failed login attempt for user: {$request->email}",
                $request->ip(),
                $request->userAgent(),
                ['email' => $request->email],
                'warning'
            );

            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        // Update last login information
        $user->updateLastLogin($request->ip());

        // Log successful login
        SystemActivityLog::logActivity(
            $user->tenant_id,
            $user->id,
            'login',
            'auth',
            "User logged in: {$user->email}",
            $request->ip(),
            $request->userAgent(),
            ['device_name' => $request->device_name],
            'info'
        );

        $token = $user->createToken($request->device_name)->plainTextToken;

        return response()->json([
            'message' => 'Login successful',
            'access_token' => $token,
            'token_type' => 'Bearer',
            'user' => $user->fresh(),
        ]);
    }

    /**
     * Handle user registration.
     */
    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'device_name' => 'required|string',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'tenant_id' => 1, // Default tenant for mobile app users
            'role' => 'user', // Default role for app customers
            'account_status' => 'active',
        ]);

        // Log registration
        SystemActivityLog::logActivity(
            $user->tenant_id,
            $user->id,
            'user_registered',
            'auth',
            "New user registered: {$user->email}",
            $request->ip(),
            $request->userAgent(),
            ['user_id' => $user->id],
            'info'
        );

        $token = $user->createToken($request->device_name)->plainTextToken;

        return response()->json([
            'message' => 'Registration successful',
            'access_token' => $token,
            'token_type' => 'Bearer',
            'user' => $user->fresh(),
        ], 201);
    }

    /**
     * Log the user out (revoke token).
     */
    public function logout(Request $request)
    {
        $user = $request->user();
        
        // Log logout
        SystemActivityLog::logActivity(
            $user->tenant_id,
            $user->id,
            'logout',
            'auth',
            "User logged out: {$user->email}",
            $request->ip(),
            $request->userAgent(),
            null,
            'info'
        );

        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Logged out successfully'
        ]);
    }

    /**
     * Get the authenticated user.
     */
    public function user(Request $request)
    {
        return response()->json($request->user()->fresh());
    }

    /**
     * Update user profile information (supports avatar upload).
     */
    public function updateProfile(Request $request)
    {
        $user = $request->user();

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'phone' => 'nullable|string|max:20',
            'avatar' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        // Update basic info
        $user->update($request->only('name', 'email', 'phone'));

        // Handle avatar upload
        if ($request->hasFile('avatar')) {
            // Delete old avatar if exists
            if ($user->avatar_path) {
                Storage::disk('public')->delete($user->avatar_path);
            }

            // Store new avatar
            $path = $request->file('avatar')->store('avatars', 'public');
            $user->update(['avatar_path' => $path]);
        }

        // Log profile update
        SystemActivityLog::logActivity(
            $user->tenant_id,
            $user->id,
            'profile_updated',
            'user',
            "User updated profile: {$user->email}",
            $request->ip(),
            $request->userAgent(),
            ['updated_fields' => array_keys($request->only('name', 'email', 'phone'))],
            'info'
        );

        // Return fresh user data with avatar_url
        $freshUser = $user->fresh();
        
        return response()->json([
            'message' => 'Profile updated successfully',
            'user' => $freshUser,
        ]);
    }

    /**
     * Return user data with a computed avatar_url.
     */
    protected function userWithAvatar(User $user): array
    {
        $data = $user->toArray();
        $data['avatar_url'] = $user->avatar_path
            ? url('storage/' . $user->avatar_path)
            : null;

        return $data;
    }
}
