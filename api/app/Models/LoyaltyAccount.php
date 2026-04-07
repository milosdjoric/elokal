<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LoyaltyAccount extends Model
{
    protected $fillable = [
        'user_id', 'points_balance', 'points_earned_total', 'points_spent_total', 'tier',
    ];

    public const TIERS = [
        'bronze' => 0,
        'silver' => 1000,
        'gold' => 5000,
        'platinum' => 15000,
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(LoyaltyTransaction::class)->orderByDesc('created_at');
    }

    public function addPoints(int $points, string $type, ?string $description = null, ?int $orderId = null): void
    {
        $this->increment('points_balance', $points);
        $this->increment('points_earned_total', $points);

        $this->transactions()->create([
            'points' => $points,
            'type' => $type,
            'description' => $description,
            'order_id' => $orderId,
        ]);

        $this->recalculateTier();
    }

    public function spendPoints(int $points, ?int $orderId = null): bool
    {
        if ($this->points_balance < $points) return false;

        $this->decrement('points_balance', $points);
        $this->increment('points_spent_total', $points);

        $this->transactions()->create([
            'points' => -$points,
            'type' => 'spend',
            'description' => 'Iskorišćeno na narudžbini.',
            'order_id' => $orderId,
        ]);

        return true;
    }

    public function recalculateTier(): void
    {
        $earned = $this->points_earned_total;
        $newTier = 'bronze';

        foreach (array_reverse(self::TIERS) as $tier => $threshold) {
            if ($earned >= $threshold) {
                $newTier = $tier;
                break;
            }
        }

        if ($this->tier !== $newTier) {
            $this->update(['tier' => $newTier]);
        }
    }

    public function getNextTierAttribute(): ?array
    {
        $tiers = array_keys(self::TIERS);
        $currentIndex = array_search($this->tier, $tiers);
        if ($currentIndex === false || $currentIndex >= count($tiers) - 1) return null;

        $nextTier = $tiers[$currentIndex + 1];
        return [
            'name' => $nextTier,
            'points_needed' => self::TIERS[$nextTier] - $this->points_earned_total,
            'threshold' => self::TIERS[$nextTier],
        ];
    }
}
