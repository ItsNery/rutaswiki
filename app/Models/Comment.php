<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Comment extends Model
{
    protected $fillable = [
        'transit_route_id',
        'user_id',
        'body',
    ];

    public function transitRoute(): BelongsTo
    {
        return $this->belongsTo(TransitRoute::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
