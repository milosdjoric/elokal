<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockNotification extends Model
{
    protected $fillable = ['product_id', 'email', 'notified', 'notified_at'];

    protected function casts(): array
    {
        return [
            'notified' => 'boolean',
            'notified_at' => 'datetime',
        ];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function scopePending($query)
    {
        return $query->where('notified', false);
    }
}
