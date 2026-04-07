<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ShippingMethod extends Model
{
    protected $fillable = [
        'shipping_zone_id', 'name', 'type', 'cost',
        'free_above', 'per_kg_cost', 'estimated_days',
        'is_active', 'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'cost' => 'decimal:2',
            'free_above' => 'decimal:2',
            'per_kg_cost' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    public const TYPES = ['flat', 'weight_based', 'price_based', 'free'];

    public function zone(): BelongsTo
    {
        return $this->belongsTo(ShippingZone::class, 'shipping_zone_id');
    }

    public function calculateCost(float $subtotal, float $weight = 0): float
    {
        if ($this->type === 'free') {
            return 0;
        }

        if ($this->free_above && $subtotal >= (float) $this->free_above) {
            return 0;
        }

        return match ($this->type) {
            'flat' => (float) $this->cost,
            'weight_based' => (float) $this->cost + ($weight * (float) ($this->per_kg_cost ?? 0)),
            'price_based' => (float) $this->cost,
            default => (float) $this->cost,
        };
    }
}
