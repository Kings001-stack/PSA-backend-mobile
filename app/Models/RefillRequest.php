<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RefillRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'tenant_id',
        'user_id',
        'medication_id',
        'prescription_id',
        'quantity',
        'notes',
        'prescription_document_path',
        'status',
        'admin_notes',
        'rejection_reason',
        'reviewed_at',
        'reviewed_by',
        'viewed_at',
        'viewed_by',
        'user_viewed_at',
        'ready_at',
        'collected_at',
        'collected_by',
        'preferred_pickup_time',
        'is_urgent',
    ];

    protected function casts(): array
    {
        return [
            'reviewed_at' => 'datetime',
            'viewed_at' => 'datetime',
            'user_viewed_at' => 'datetime',
            'ready_at' => 'datetime',
            'collected_at' => 'datetime',
            'preferred_pickup_time' => 'datetime',
            'is_urgent' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::addGlobalScope('tenant', function ($query) {
            if (auth()->check() && auth()->user()->tenant_id) {
                $query->where($query->getModel()->getTable() . '.tenant_id', auth()->user()->tenant_id);
            }
        });

        // Log creation
        static::created(function ($refillRequest) {
            RefillAuditLog::logAction(
                $refillRequest,
                'created',
                null,
                $refillRequest->status,
                'Refill request created by user'
            );
        });
    }

    // Relationships
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function medication(): BelongsTo
    {
        return $this->belongsTo(Medication::class);
    }

    public function prescription(): BelongsTo
    {
        return $this->belongsTo(Prescription::class);
    }

    public function reviewedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function viewedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'viewed_by');
    }

    public function collectedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'collected_by');
    }

    public function auditLogs(): HasMany
    {
        return $this->hasMany(RefillAuditLog::class);
    }

    // Scopes
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeUnderReview($query)
    {
        return $query->where('status', 'under_review');
    }

    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    public function scopeReadyForPickup($query)
    {
        return $query->where('status', 'ready_for_pickup');
    }

    public function scopeUrgent($query)
    {
        return $query->where('is_urgent', true);
    }

    // Status transition methods
    public function canTransitionTo(string $newStatus): bool
    {
        $allowedTransitions = [
            'pending' => ['under_review', 'approved', 'rejected', 'cancelled'],
            'under_review' => ['approved', 'rejected', 'pending'],
            'approved' => ['ready_for_pickup', 'cancelled'],
            'rejected' => [],
            'ready_for_pickup' => ['collected', 'cancelled'],
            'collected' => [],
            'cancelled' => [],
        ];

        return in_array($newStatus, $allowedTransitions[$this->status] ?? []);
    }

    public function transitionTo(string $newStatus, ?string $notes = null, ?array $metadata = null): bool
    {
        if (!$this->canTransitionTo($newStatus)) {
            return false;
        }

        $previousStatus = $this->status;
        $this->status = $newStatus;

        // Set timestamps based on status
        if ($newStatus === 'ready_for_pickup') {
            $this->ready_at = now();
        } elseif ($newStatus === 'collected') {
            $this->collected_at = now();
            $this->collected_by = auth()->id();
        }

        if (in_array($newStatus, ['approved', 'rejected'])) {
            $this->reviewed_at = now();
            $this->reviewed_by = auth()->id();
        }

        $this->save();

        // Log the transition
        RefillAuditLog::logAction(
            $this,
            'status_changed',
            $previousStatus,
            $newStatus,
            $notes,
            $metadata
        );

        return true;
    }
}

