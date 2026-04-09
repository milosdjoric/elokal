<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class GiftCard extends Model
{
    protected $fillable = [
        'code', 'initial_amount', 'balance', 'purchased_by',
        'recipient_email', 'recipient_name', 'message',
        'is_active', 'expires_at',
    ];

    protected function casts(): array
    {
        return [
            'initial_amount' => 'decimal:2',
            'balance' => 'decimal:2',
            'is_active' => 'boolean',
            'expires_at' => 'datetime',
        ];
    }

    public function purchaser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'purchased_by');
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(GiftCardTransaction::class)->orderByDesc('created_at');
    }

    public static function generateCode(): string
    {
        do {
            $code = strtoupper(Str::random(4) . '-' . Str::random(4) . '-' . Str::random(4));
        } while (self::where('code', $code)->exists());
        return $code;
    }

    public function isValid(): bool|string
    {
        return $this->isUsable();
    }

    public function isUsable(): bool|string
    {
        if (! $this->is_active) return 'Poklon kartica nije aktivna.';
        if ($this->expires_at && $this->expires_at->isPast()) return 'Poklon kartica je istekla.';
        if ((float) $this->balance <= 0) return 'Poklon kartica nema sredstava.';
        return true;
    }

    public function redeem(float $amount, ?int $orderId = null): float
    {
        $redeemed = min($amount, (float) $this->balance);
        $this->decrement('balance', $redeemed);

        $this->transactions()->create([
            'order_id' => $orderId,
            'amount' => -$redeemed,
            'type' => 'redemption',
            'balance_after' => $this->balance,
        ]);

        return $redeemed;
    }
}
