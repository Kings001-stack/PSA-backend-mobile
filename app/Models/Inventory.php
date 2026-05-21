<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Inventory extends Model
{
    use HasFactory;

    protected $table = 'inventory';

    protected $fillable = [
        'tenant_id',
        'medication_id',
        'quantity',
        'reorder_level',
        'reorder_quantity',
        'expiry_date',
        'batch_number',
        'supplier',
        'unit_price',
        'unit_cost',
        'selling_price',
        'markup_percentage',
        'low_stock_alert_sent',
        'last_restocked_at',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'expiry_date' => 'date',
            'unit_price' => 'decimal:2',
            'unit_cost' => 'decimal:2',
            'selling_price' => 'decimal:2',
            'markup_percentage' => 'decimal:2',
            'low_stock_alert_sent' => 'boolean',
            'last_restocked_at' => 'datetime',
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

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function medication(): BelongsTo
    {
        return $this->belongsTo(Medication::class);
    }
}
