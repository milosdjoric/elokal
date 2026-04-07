<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    protected $fillable = [
        'order_number', 'user_id', 'status', 'email', 'phone',
        'shipping_first_name', 'shipping_last_name', 'shipping_company',
        'shipping_address_line_1', 'shipping_address_line_2',
        'shipping_city', 'shipping_postal_code', 'shipping_country',
        'billing_first_name', 'billing_last_name',
        'billing_address_line_1', 'billing_city', 'billing_postal_code', 'billing_country',
        'subtotal', 'shipping_cost', 'tax', 'discount', 'total',
        'notes', 'admin_notes',
    ];

    protected function casts(): array
    {
        return [
            'subtotal' => 'decimal:2',
            'shipping_cost' => 'decimal:2',
            'tax' => 'decimal:2',
            'discount' => 'decimal:2',
            'total' => 'decimal:2',
        ];
    }

    public const STATUSES = [
        'pending', 'confirmed', 'processing', 'shipped',
        'delivered', 'completed', 'cancelled', 'refunded',
    ];

    public static function generateOrderNumber(): string
    {
        $prefix = 'EL';
        $date = now()->format('ymd');
        $random = strtoupper(substr(uniqid(), -4));
        return "{$prefix}-{$date}-{$random}";
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function timeline(): HasMany
    {
        return $this->hasMany(OrderTimeline::class)->orderBy('created_at');
    }
}
