<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockReservation extends Model
{
    protected $fillable = ['product_id', 'session_id', 'quantity', 'expires_at'];

    protected function casts(): array
    {
        return ['expires_at' => 'datetime'];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function scopeActive($query)
    {
        return $query->where('expires_at', '>', now());
    }

    public function scopeExpired($query)
    {
        return $query->where('expires_at', '<=', now());
    }

    public static function reserve(int $productId, string $sessionId, int $quantity, int $minutes = 15): self
    {
        // Ukloni staru rezervaciju za isti proizvod/sesiju
        self::where('product_id', $productId)
            ->where('session_id', $sessionId)
            ->delete();

        $product = Product::findOrFail($productId);
        $available = $product->stock_quantity - self::active()->where('product_id', $productId)->sum('quantity');

        if ($available < $quantity) {
            throw new \RuntimeException("Nedovoljno na stanju za rezervaciju.");
        }

        return self::create([
            'product_id' => $productId,
            'session_id' => $sessionId,
            'quantity' => $quantity,
            'expires_at' => now()->addMinutes($minutes),
        ]);
    }

    public static function releaseExpired(): int
    {
        return self::expired()->delete();
    }

    public static function releaseForSession(string $sessionId): int
    {
        return self::where('session_id', $sessionId)->delete();
    }
}
