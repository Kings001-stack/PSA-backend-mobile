<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RefillAuditLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'tenant_id',
        'refill_request_id',
        'user_id',
        'action',
        'previous_status',
        'new_status',
        'notes',
        'metadata',
        'ip_address',
        'user_agent',
    ];

    protected function casts(): array
    {
        return [
            'metadata' => 'array',
        ];
    }

    protected static function booted(): void
    {
        static::addGlobalScope('tenant', function ($query) {
            if (auth()->check() && auth()->user()->tenant_id) {
                $query->where($query->getModel()->getTable() . '.tenant_id', auth()->user()->tenant_id);
            }
        });
    }

    // Relationships
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function refillRequest(): BelongsTo
    {
        return $this->belongsTo(RefillRequest::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // Helper method to create audit log
    public static function logAction(
        RefillRequest $refillRequest,
        string $action,
        ?string $previousStatus = null,
        ?string $newStatus = null,
        ?string $notes = null,
        ?array $metadata = null
    ): self {
        return self::create([
            'tenant_id' => $refillRequest->tenant_id,
            'refill_request_id' => $refillRequest->id,
            'user_id' => auth()->id(),
            'action' => $action,
            'previous_status' => $previousStatus,
            'new_status' => $newStatus,
            'notes' => $notes,
            'metadata' => $metadata,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }
}
