<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Coupon extends Model
{
    use HasFactory;

    protected $fillable = [
        'code', 'type', 'value', 'min_order_amount', 'max_discount_amount',
        'max_uses', 'max_uses_per_user', 'times_used',
        'starts_at', 'expires_at', 'is_active', 'description',
    ];

    protected function casts(): array
    {
        return [
            'value' => 'decimal:2',
            'min_order_amount' => 'decimal:2',
            'max_discount_amount' => 'decimal:2',
            'is_active' => 'boolean',
            'starts_at' => 'datetime',
            'expires_at' => 'datetime',
        ];
    }

    public const TYPES = ['percentage', 'fixed_amount', 'free_shipping'];

    public function usages(): HasMany
    {
        return $this->hasMany(CouponUsage::class);
    }

    public function isValid(?int $userId = null): bool|string
    {
        if (! $this->is_active) {
            return 'Kupon nije aktivan.';
        }

        if ($this->starts_at && $this->starts_at->isFuture()) {
            return 'Kupon još nije počeo da važi.';
        }

        if ($this->expires_at && $this->expires_at->isPast()) {
            return 'Kupon je istekao.';
        }

        if ($this->max_uses && $this->times_used >= $this->max_uses) {
            return 'Kupon je iskorišćen maksimalan broj puta.';
        }

        if ($userId && $this->max_uses_per_user) {
            $userUsages = $this->usages()->where('user_id', $userId)->count();
            if ($userUsages >= $this->max_uses_per_user) {
                return 'Već ste iskoristili ovaj kupon.';
            }
        }

        return true;
    }

    public function calculateDiscount(float $subtotal): float
    {
        if ($this->min_order_amount && $subtotal < (float) $this->min_order_amount) {
            return 0;
        }

        $discount = match ($this->type) {
            'percentage' => $subtotal * ((float) $this->value / 100),
            'fixed_amount' => (float) $this->value,
            'free_shipping' => 0, // Handled separately
            default => 0,
        };

        if ($this->max_discount_amount && $discount > (float) $this->max_discount_amount) {
            $discount = (float) $this->max_discount_amount;
        }

        return min($discount, $subtotal);
    }
}
