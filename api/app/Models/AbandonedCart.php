<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class AbandonedCart extends Model
{
    protected $fillable = [
        'email', 'user_id', 'items', 'total', 'token',
        'status', 'emails_sent', 'last_email_at', 'recovered_at',
    ];

    protected function casts(): array
    {
        return [
            'items' => 'array',
            'total' => 'decimal:2',
            'last_email_at' => 'datetime',
            'recovered_at' => 'datetime',
        ];
    }

    public const STATUSES = ['abandoned', 'recovered', 'expired'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public static function generateToken(): string
    {
        return Str::random(64);
    }
}
