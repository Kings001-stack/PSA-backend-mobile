<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;

class Prescription extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'tenant_id',
        'user_id',
        'medication_id',
        'prescription_number',
        'prescriber_name',
        'prescriber_license',
        'prescribed_date',
        'expiration_date',
        'refills_allowed',
        'refills_remaining',
        'dosage',
        'frequency',
        'instructions',
        'is_controlled',
        'controlled_schedule',
        'document_path',
        'status',
        'is_verified',
        'verified_by',
        'verified_at',
    ];

    protected function casts(): array
    {
        return [
            'prescribed_date' => 'date',
            'expiration_date' => 'date',
            'is_controlled' => 'boolean',
            'is_verified' => 'boolean',
            'verified_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::addGlobalScope('tenant', function ($query) {
            if (auth()->check() && auth()->user()->tenant_id) {
                $query->where($query->getModel()->getTable() . '.tenant_id', auth()->user()->tenant_id);
            }
        });

        // Auto-update status based on expiration
        static::saving(function ($prescription) {
            if ($prescription->expiration_date && Carbon::parse($prescription->expiration_date)->isPast()) {
                $prescription->status = 'expired';
            }
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

    public function verifiedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    public function refillRequests(): HasMany
    {
        return $this->hasMany(RefillRequest::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', 'active')
            ->where('expiration_date', '>=', now());
    }

    public function scopeExpired($query)
    {
        return $query->where(function ($q) {
            $q->where('status', 'expired')
              ->orWhere('expiration_date', '<', now());
        });
    }

    public function scopeControlled($query)
    {
        return $query->where('is_controlled', true);
    }

    public function scopeVerified($query)
    {
        return $query->where('is_verified', true);
    }

    // Helper methods
    public function isExpired(): bool
    {
        return $this->expiration_date && Carbon::parse($this->expiration_date)->isPast();
    }

    public function hasRefillsRemaining(): bool
    {
        return $this->refills_remaining > 0;
    }

    public function canRefill(): bool
    {
        return $this->status === 'active' 
            && !$this->isExpired() 
            && $this->hasRefillsRemaining()
            && $this->is_verified;
    }

    public function decrementRefills(): void
    {
        if ($this->refills_remaining > 0) {
            $this->decrement('refills_remaining');
        }
    }
}
