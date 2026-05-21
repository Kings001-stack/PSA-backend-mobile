<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SystemActivityLog extends Model
{
    protected $fillable = [
        'tenant_id',
        'user_id',
        'event_type',
        'event_category',
        'description',
        'ip_address',
        'user_agent',
        'context',
        'severity',
        // Legacy columns (nullable)
        'action',
        'model_type',
        'model_id',
        'old_values',
        'new_values',
    ];

    protected $casts = [
        'context' => 'array',
        'old_values' => 'array',
        'new_values' => 'array',
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
     * Log a system activity
     */
    public static function logActivity(
        int $tenantId,
        ?int $userId,
        string $eventType,
        string $eventCategory,
        string $description,
        ?string $ipAddress = null,
        ?string $userAgent = null,
        ?array $context = null,
        string $severity = 'info'
    ): self {
        return self::create([
            'tenant_id' => $tenantId,
            'user_id' => $userId,
            'event_type' => $eventType,
            'event_category' => $eventCategory,
            'description' => $description,
            'ip_address' => $ipAddress,
            'user_agent' => $userAgent,
            'context' => $context,
            'severity' => $severity,
        ]);
    }
}
