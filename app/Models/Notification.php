<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Notification extends Model
{
    protected $fillable = [
        'tenant_id',
        'user_id',
        'type',
        'title',
        'message',
        'severity',
        'read_at',
        'metadata',
    ];

    protected $casts = [
        'read_at' => 'datetime',
        'metadata' => 'array',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Mark notification as read
     */
    public function markAsRead(): void
    {
        $this->update(['read_at' => now()]);
    }

    /**
     * Check if notification is unread
     */
    public function isUnread(): bool
    {
        return is_null($this->read_at);
    }

    /**
     * Create a notification for a user
     */
    public static function createNotification(
        int $tenantId,
        int $userId,
        string $type,
        string $title,
        string $message,
        string $severity = 'info',
        ?array $metadata = null
    ): self {
        return self::create([
            'tenant_id' => $tenantId,
            'user_id' => $userId,
            'type' => $type,
            'title' => $title,
            'message' => $message,
            'severity' => $severity,
            'metadata' => $metadata,
        ]);
    }

    /**
     * Notify all super admins
     */
    public static function notifySuperAdmins(
        int $tenantId,
        string $type,
        string $title,
        string $message,
        string $severity = 'info',
        ?array $metadata = null
    ): void {
        $superAdmins = User::where('tenant_id', $tenantId)
            ->where('role', 'super_admin')
            ->where('account_status', 'active')
            ->get();

        foreach ($superAdmins as $admin) {
            self::createNotification($tenantId, $admin->id, $type, $title, $message, $severity, $metadata);
        }
    }
}
