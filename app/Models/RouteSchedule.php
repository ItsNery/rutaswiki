<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RouteSchedule extends Model
{
    protected $fillable = [
        'transit_route_id',
        'day_type',
        'start_time',
        'end_time',
        'frequency_minutes',
    ];

    public function transitRoute(): BelongsTo
    {
        return $this->belongsTo(TransitRoute::class, 'transit_route_id');
    }
}
