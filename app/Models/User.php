<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'tenant_id',
        'name',
        'email',
        'password',
        'role',
        'account_status',
        'phone',
        'avatar_path',
        'last_login_at',
        'last_login_ip',
        'mfa_enabled',
        'mfa_secret',
        'created_by',
        'updated_by',
        'notify_refill_status',
        'notify_prescription_reminders',
        'notify_pharmacy_updates',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'last_login_at' => 'datetime',
            'mfa_enabled' => 'boolean',
            'notify_refill_status' => 'boolean',
            'notify_prescription_reminders' => 'boolean',
            'notify_pharmacy_updates' => 'boolean',
        ];
    }

    /**
     * Append avatar_url to the model's array form.
     *
     * @var array<int, string>
     */
    protected $appends = ['avatar_url'];

    /**
     * Get the avatar URL accessor.
     */
    public function getAvatarUrlAttribute(): ?string
    {
        return $this->avatar_path
            ? url('storage/' . $this->avatar_path)
            : null;
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * Get the user's push tokens
     */
    public function pushTokens(): HasMany
    {
        return $this->hasMany(PushToken::class);
    }

    /**
     * Check if the user has a specific role.
     */
    public function hasRole(string $role): bool
    {
        return $this->role === $role;
    }

    /**
     * Check if the user is a pharmacist (has dashboard access).
     */
    public function isPharmacist(): bool
    {
        return $this->role === 'pharmacist';
    }

    /**
     * Check if the user is an admin (alias for isPharmacist for backward compatibility).
     */
    public function isAdmin(): bool
    {
        return $this->isPharmacist();
    }

    /**
     * Check if the user is a super admin.
     */
    public function isSuperAdmin(): bool
    {
        return $this->role === 'super_admin';
    }

    /**
     * Check if the user is a regular user (customer/patient).
     */
    public function isUser(): bool
    {
        return $this->role === 'user';
    }

    /**
     * Check if user has dashboard access (pharmacist or super_admin).
     */
    public function hasDashboardAccess(): bool
    {
        return in_array($this->role, ['pharmacist', 'super_admin']);
    }

    /**
     * Check if the user account is active.
     */
    public function isActive(): bool
    {
        return $this->account_status === 'active';
    }

    /**
     * Check if the user account is suspended.
     */
    public function isSuspended(): bool
    {
        return $this->account_status === 'suspended';
    }

    /**
     * Get role hierarchy level (higher number = more privileges).
     */
    public function getRoleLevel(): int
    {
        return match($this->role) {
            'super_admin' => 3,
            'pharmacist' => 2,
            'user' => 1,
            default => 0,
        };
    }

    /**
     * Check if this user can manage another user based on role hierarchy.
     */
    public function canManage(User $targetUser): bool
    {
        return $this->getRoleLevel() > $targetUser->getRoleLevel();
    }

    /**
     * Get the user who created this account.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the user who last updated this account.
     */
    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Get user's notifications.
     */
    public function notifications(): HasMany
    {
        return $this->hasMany(Notification::class);
    }

    /**
     * Get user's activity logs.
     */
    public function activityLogs(): HasMany
    {
        return $this->hasMany(SystemActivityLog::class);
    }

    /**
     * Update last login information.
     */
    public function updateLastLogin(string $ipAddress): void
    {
        $this->update([
            'last_login_at' => now(),
            'last_login_ip' => $ipAddress,
        ]);
    }
}
