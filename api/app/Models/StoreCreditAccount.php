<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class StoreCreditAccount extends Model
{
    protected $fillable = ['user_id', 'balance'];

    protected function casts(): array
    {
        return ['balance' => 'decimal:2'];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(StoreCreditTransaction::class)->orderByDesc('created_at');
    }

    public function credit(float $amount, string $reason, ?int $orderId = null): void
    {
        $this->increment('balance', $amount);

        $this->transactions()->create([
            'amount' => $amount,
            'type' => 'credit',
            'balance_after' => $this->fresh()->balance,
            'reason' => $reason,
            'order_id' => $orderId,
        ]);
    }

    public function debit(float $amount, string $reason, ?int $orderId = null): bool
    {
        if ((float) $this->balance < $amount) return false;

        $this->decrement('balance', $amount);

        $this->transactions()->create([
            'amount' => -$amount,
            'type' => 'debit',
            'balance_after' => $this->fresh()->balance,
            'reason' => $reason,
            'order_id' => $orderId,
        ]);

        return true;
    }
}
