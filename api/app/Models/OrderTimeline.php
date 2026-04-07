<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderTimeline extends Model
{
    public $timestamps = false;

    protected $table = 'order_timeline';

    protected $fillable = [
        'order_id', 'status', 'old_status', 'note',
        'actor_type', 'actor_id',
    ];

    protected function casts(): array
    {
        return ['created_at' => 'datetime'];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
}
