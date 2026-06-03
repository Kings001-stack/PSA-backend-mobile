<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\RefillRequest;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class UserController extends Controller
{
    public function index(Request $request): Response
    {
        $currentUser = $request->user();
        $search = $request->input('search');
        $roleFilter = $request->input('role');
        $statusFilter = $request->input('status');
        $perPage = $request->input('per_page', 15);
        
        $query = User::query()
            ->with('tenant:id,name')
            ->when($search, fn($q) => $q->where('name', 'like', "%{$search}%")
                ->orWhere('email', 'like', "%{$search}%")
                ->orWhere('phone', 'like', "%{$search}%"))
            ->when($roleFilter, fn($q) => $q->where('role', $roleFilter))
            ->when($statusFilter, fn($q) => $q->where('account_status', $statusFilter));

        // Pharmacists can only see regular users (patients), not other pharmacists or super_admins
        if ($currentUser->isPharmacist() && !$currentUser->isSuperAdmin()) {
            $query->where('role', 'user');
        }

        $users = $query->latest()->paginate($perPage)->through(fn ($user) => [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'phone' => $user->phone,
            'avatar_url' => $user->avatar_url,
            'role' => $user->role,
            'account_status' => $user->account_status,
            'tenant' => $user->tenant ? [
                'id' => $user->tenant->id,
                'name' => $user->tenant->name,
            ] : null,
            'last_login_at' => $user->last_login_at?->diffForHumans(),
            'last_login_date' => $user->last_login_at?->format('M d, Y h:i A'),
            'created_at' => $user->created_at->format('M d, Y'),
            'joined_date' => $user->created_at->format('F d, Y'),
            // Pharmacy-specific stats
            'refill_stats' => $this->getUserRefillStats($user),
            // Permissions
            'can_view_profile' => $this->canViewUserProfile($currentUser, $user),
            'can_view_refills' => $this->canViewUserRefills($currentUser, $user),
            'can_contact' => $this->canContactUser($currentUser, $user),
            'can_edit' => $this->canEditUser($currentUser, $user),
            'can_suspend' => $this->canSuspendUser($currentUser, $user),
            'can_delete' => $this->canDeleteUser($currentUser, $user),
        ]);

        return Inertia::render('Admin/Users/Index', [
            'users' => $users,
            'filters' => [
                'search' => $search,
                'role' => $roleFilter,
                'status' => $statusFilter,
            ],
            'permissions' => [
                'is_pharmacist' => $currentUser->isPharmacist(),
                'is_super_admin' => $currentUser->isSuperAdmin(),
                'can_create_users' => $currentUser->isSuperAdmin(),
                'can_create_pharmacists' => $currentUser->isSuperAdmin(),
                'can_manage_roles' => $currentUser->isSuperAdmin(),
            ],
        ]);
    }

    /**
     * View user profile (pharmacist-friendly view)
     */
    public function show(Request $request, User $user): Response
    {
        $currentUser = $request->user();

        // Check permissions
        if (!$this->canViewUserProfile($currentUser, $user)) {
            abort(403, 'You do not have permission to view this user profile.');
        }

        // Get refill requests with relationships
        $refillRequests = RefillRequest::where('user_id', $user->id)
            ->with(['medication', 'reviewedBy'])
            ->latest()
            ->paginate(10);

        // Get recent notifications
        $recentNotifications = Notification::where('user_id', $user->id)
            ->where('notification_type', 'pharmacy')
            ->latest()
            ->limit(10)
            ->get();

        // Pharmacy activity stats
        $refillStats = [
            'total_requests' => RefillRequest::where('user_id', $user->id)->count(),
            'pending_requests' => RefillRequest::where('user_id', $user->id)->where('status', 'pending')->count(),
            'approved_requests' => RefillRequest::where('user_id', $user->id)->where('status', 'approved')->count(),
            'rejected_requests' => RefillRequest::where('user_id', $user->id)->where('status', 'rejected')->count(),
            'ready_for_pickup' => RefillRequest::where('user_id', $user->id)->where('status', 'ready_for_pickup')->count(),
            'collected' => RefillRequest::where('user_id', $user->id)->where('status', 'collected')->count(),
            'last_refill_date' => RefillRequest::where('user_id', $user->id)->latest()->value('created_at')?->format('M d, Y'),
        ];

        // Most requested medications
        $topMedications = RefillRequest::where('user_id', $user->id)
            ->select('medication_id', DB::raw('count(*) as request_count'))
            ->groupBy('medication_id')
            ->with('medication:id,name,strength,dosage_form')
            ->orderByDesc('request_count')
            ->limit(5)
            ->get()
            ->map(fn($item) => [
                'medication_name' => $item->medication?->name ?? 'Unknown',
                'dosage' => $item->medication?->strength ?? '',
                'form' => $item->medication?->dosage_form ?? '',
                'request_count' => $item->request_count,
            ]);

        return Inertia::render('Admin/Users/Show', [
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
                'avatar_url' => $user->avatar_url,
                'account_status' => $user->account_status,
                'last_login_at' => $user->last_login_at?->format('F d, Y h:i A'),
                'created_at' => $user->created_at->format('F d, Y'),
                'tenant' => $user->tenant ? [
                    'id' => $user->tenant->id,
                    'name' => $user->tenant->name,
                ] : null,
            ],
            'refillStats' => $refillStats,
            'topMedications' => $topMedications,
            'refillRequests' => $refillRequests->through(fn ($refill) => [
                'id' => $refill->id,
                'medication_name' => $refill->medication?->name ?? 'Unknown',
                'medication_dosage' => $refill->medication?->strength ?? '',
                'medication_form' => $refill->medication?->dosage_form ?? '',
                'quantity' => $refill->quantity,
                'status' => $refill->status,
                'notes' => $refill->notes,
                'admin_notes' => $refill->admin_notes,
                'rejection_reason' => $refill->rejection_reason,
                'is_urgent' => $refill->is_urgent,
                'reviewed_at' => $refill->reviewed_at?->format('M d, Y h:i A'),
                'reviewed_by_name' => $refill->reviewedBy?->name,
                'created_at' => $refill->created_at->format('M d, Y h:i A'),
            ]),
            'recentNotifications' => $recentNotifications->map(fn ($notification) => [
                'id' => $notification->id,
                'title' => $notification->title,
                'message' => $notification->message,
                'created_at' => $notification->created_at->diffForHumans(),
            ]),
            'permissions' => [
                'can_contact' => $this->canContactUser($currentUser, $user),
                'can_view_refills' => $this->canViewUserRefills($currentUser, $user),
                'is_pharmacist' => $currentUser->isPharmacist(),
                'is_super_admin' => $currentUser->isSuperAdmin(),
            ],
        ]);
    }

    /**
     * Send notification to user (pharmacist can use this)
     */
    public function sendNotification(Request $request, User $user)
    {
        $currentUser = $request->user();

        // Check permissions
        if (!$this->canContactUser($currentUser, $user)) {
            return back()->withErrors(['error' => 'You do not have permission to send notifications to this user.']);
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'message' => 'required|string|max:1000',
            'notification_type' => 'required|in:pharmacy,refill,system,alert',
        ]);

        // Create notification
        Notification::create([
            'tenant_id' => $user->tenant_id,
            'user_id' => $user->id,
            'title' => $validated['title'],
            'message' => $validated['message'],
            'notification_type' => $validated['notification_type'],
            'status' => 'unread',
        ]);

        // TODO: Send push notification if enabled
        // PushNotificationService::send($user, $validated['title'], $validated['message']);

        return back()->with('success', 'Notification sent successfully to ' . $user->name);
    }

    /**
     * Get user refill stats (helper method)
     */
    private function getUserRefillStats(User $user): array
    {
        return [
            'total' => RefillRequest::where('user_id', $user->id)->count(),
            'pending' => RefillRequest::where('user_id', $user->id)->where('status', 'pending')->count(),
            'active' => RefillRequest::where('user_id', $user->id)
                ->whereIn('status', ['pending', 'under_review', 'approved', 'ready_for_pickup'])
                ->count(),
            'last_refill' => RefillRequest::where('user_id', $user->id)
                ->latest()
                ->value('created_at')?->diffForHumans(),
        ];
    }

    public function store(Request $request)
    {
        $currentUser = $request->user();

        // Only super admins can create users
        if (!$currentUser->isSuperAdmin()) {
            return back()->withErrors(['error' => 'You do not have permission to create users.']);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:8|confirmed',
            'role' => ['required', Rule::in(['user', 'pharmacist', 'super_admin'])],
            'phone' => 'nullable|string|max:20',
        ]);

        $validated['password'] = Hash::make($validated['password']);
        $validated['created_by'] = $currentUser->id;
        $validated['tenant_id'] = $currentUser->tenant_id;

        User::create($validated);

        return back()->with('success', 'User created successfully.');
    }

    public function update(Request $request, User $user)
    {
        $currentUser = $request->user();

        // Check if current user can edit this user
        if (!$this->canEditUser($currentUser, $user)) {
            return back()->withErrors(['error' => 'You do not have permission to edit this user.']);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'email', Rule::unique('users')->ignore($user->id)],
            'role' => ['required', Rule::in(['user', 'pharmacist', 'super_admin'])],
            'phone' => 'nullable|string|max:20',
        ]);

        // Prevent role escalation - pharmacists can't change roles
        if (!$currentUser->isSuperAdmin()) {
            unset($validated['role']);
        }

        $validated['updated_by'] = $currentUser->id;
        $user->update($validated);

        return back()->with('success', 'User updated successfully.');
    }

    public function suspend(Request $request, User $user)
    {
        $currentUser = $request->user();

        // Check if current user can suspend this user
        if (!$this->canSuspendUser($currentUser, $user)) {
            return back()->withErrors(['error' => 'You do not have permission to suspend this user.']);
        }

        // Prevent self-suspension
        if ($user->id === $currentUser->id) {
            return back()->withErrors(['error' => 'You cannot suspend your own account.']);
        }

        $user->update([
            'account_status' => 'suspended',
            'updated_by' => $currentUser->id,
        ]);

        return back()->with('success', 'User suspended successfully.');
    }

    public function activate(Request $request, User $user)
    {
        $currentUser = $request->user();

        // Check if current user can activate this user
        if (!$this->canSuspendUser($currentUser, $user)) {
            return back()->withErrors(['error' => 'You do not have permission to activate this user.']);
        }

        $user->update([
            'account_status' => 'active',
            'updated_by' => $currentUser->id,
        ]);

        return back()->with('success', 'User activated successfully.');
    }

    /**
     * Permission checks for pharmacist user management
     */

    private function canViewUserProfile(User $currentUser, User $targetUser): bool
    {
        // Super admins can view anyone
        if ($currentUser->isSuperAdmin()) {
            return true;
        }

        // Pharmacists can only view regular users (patients)
        if ($currentUser->isPharmacist()) {
            return $targetUser->isUser();
        }

        return false;
    }

    private function canViewUserRefills(User $currentUser, User $targetUser): bool
    {
        // Same as profile view - pharmacists can view refills for patients
        return $this->canViewUserProfile($currentUser, $targetUser);
    }

    private function canContactUser(User $currentUser, User $targetUser): bool
    {
        // Pharmacists can contact patients
        if ($currentUser->isPharmacist()) {
            return $targetUser->isUser();
        }

        // Super admins can contact anyone
        if ($currentUser->isSuperAdmin()) {
            return true;
        }

        return false;
    }

    private function canEditUser(User $currentUser, User $targetUser): bool
    {
        // Super admins can edit anyone
        if ($currentUser->isSuperAdmin()) {
            return true;
        }

        // Pharmacists CANNOT edit user profiles (read-only)
        return false;
    }

    private function canSuspendUser(User $currentUser, User $targetUser): bool
    {
        // Only super admins can suspend/activate users
        if (!$currentUser->isSuperAdmin()) {
            return false;
        }

        // Cannot suspend yourself
        if ($currentUser->id === $targetUser->id) {
            return false;
        }

        return true;
    }

    private function canDeleteUser(User $currentUser, User $targetUser): bool
    {
        // Only super admins can delete users
        // Pharmacists have NO delete permissions
        if (!$currentUser->isSuperAdmin()) {
            return false;
        }

        // Cannot delete yourself
        if ($currentUser->id === $targetUser->id) {
            return false;
        }

        return true;
    }
}
