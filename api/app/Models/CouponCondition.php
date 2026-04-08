<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CouponCondition extends Model
{
    protected $fillable = ['coupon_id', 'type', 'operator', 'value'];

    public const TYPES = [
        'min_items',             // Minimum stavki u korpi
        'max_items',             // Maksimum stavki
        'specific_products',     // Samo za određene proizvode (value = JSON array product IDs)
        'specific_categories',   // Samo za određene kategorije (value = JSON array category IDs)
        'first_order',           // Samo za prvu narudžbinu korisnika (value = "1")
        'user_registered_days',  // Korisnik registrovan bar X dana
    ];

    public const OPERATORS = ['eq', 'gt', 'lt', 'gte', 'lte', 'in', 'not_in'];

    public function coupon(): BelongsTo
    {
        return $this->belongsTo(Coupon::class);
    }

    public function evaluate(array $context): bool
    {
        return match ($this->type) {
            'min_items' => $this->compareNumeric($context['items_count'] ?? 0),
            'max_items' => $this->compareNumeric($context['items_count'] ?? 0),
            'specific_products' => $this->checkInList($context['product_ids'] ?? []),
            'specific_categories' => $this->checkInList($context['category_ids'] ?? []),
            'first_order' => ($context['user_orders_count'] ?? 0) === 0,
            'user_registered_days' => $this->compareNumeric($context['user_registered_days'] ?? 0),
            default => true,
        };
    }

    private function compareNumeric(float $actual): bool
    {
        $expected = (float) $this->value;
        return match ($this->operator) {
            'eq' => $actual == $expected,
            'gt' => $actual > $expected,
            'lt' => $actual < $expected,
            'gte' => $actual >= $expected,
            'lte' => $actual <= $expected,
            default => true,
        };
    }

    private function checkInList(array $actualIds): bool
    {
        $conditionIds = json_decode($this->value, true) ?: [];
        if ($this->operator === 'in') {
            return count(array_intersect($actualIds, $conditionIds)) > 0;
        }
        if ($this->operator === 'not_in') {
            return count(array_intersect($actualIds, $conditionIds)) === 0;
        }
        return true;
    }
}
