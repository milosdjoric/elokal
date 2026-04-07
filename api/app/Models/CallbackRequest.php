<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CallbackRequest extends Model
{
    protected $fillable = [
        'product_id', 'name', 'phone', 'channel', 'message',
        'status', 'admin_notes',
    ];

    public const CHANNELS = ['call', 'sms', 'whatsapp'];
    public const STATUSES = ['pending', 'contacted', 'closed'];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
