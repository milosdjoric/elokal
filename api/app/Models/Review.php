<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Review extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id', 'user_id', 'rating', 'title', 'content',
        'is_verified_purchase', 'status', 'admin_reply', 'admin_replied_at',
    ];

    protected function casts(): array
    {
        return [
            'rating' => 'integer',
            'is_verified_purchase' => 'boolean',
            'admin_replied_at' => 'datetime',
        ];
    }

    public const STATUSES = ['pending', 'approved', 'rejected'];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function helpful(): HasMany
    {
        return $this->hasMany(ReviewHelpful::class);
    }

    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }
}
