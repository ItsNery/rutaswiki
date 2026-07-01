<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Stop extends Model
{
    protected $fillable = [
        'transit_route_id',
        'name',
        'latitude',
        'longitude',
        'order',
        'description',
    ];

    public function transitRoute(): BelongsTo
    {
        return $this->belongsTo(TransitRoute::class);
    }
}
