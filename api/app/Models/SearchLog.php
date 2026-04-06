<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SearchLog extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'query',
        'results_count',
        'user_id',
        'ip',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
