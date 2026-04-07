<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockMovement extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'product_id', 'quantity', 'type', 'reference_type',
        'reference_id', 'note', 'stock_after',
    ];

    protected function casts(): array
    {
        return ['created_at' => 'datetime'];
    }

    public const TYPES = ['sale', 'return', 'adjustment', 'restock', 'cancellation'];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public static function record(Product $product, int $quantity, string $type, ?string $note = null, ?string $refType = null, ?int $refId = null): self
    {
        return self::create([
            'product_id' => $product->id,
            'quantity' => $quantity,
            'type' => $type,
            'reference_type' => $refType,
            'reference_id' => $refId,
            'note' => $note,
            'stock_after' => $product->stock_quantity,
        ]);
    }
}
