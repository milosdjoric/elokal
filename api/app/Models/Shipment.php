<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Shipment extends Model
{
    protected $fillable = [
        'order_id', 'tracking_number', 'carrier', 'carrier_url',
        'status', 'weight', 'notes', 'shipped_at', 'delivered_at',
    ];

    protected function casts(): array
    {
        return [
            'weight' => 'decimal:2',
            'shipped_at' => 'datetime',
            'delivered_at' => 'datetime',
        ];
    }

    public const STATUSES = ['pending', 'picked_up', 'in_transit', 'delivered', 'returned'];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
}
