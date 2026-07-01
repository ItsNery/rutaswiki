<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RouteRevision extends Model
{
    protected $fillable = [
        'transit_route_id',
        'user_id',
        'geometry',
        'stops_snapshot',
        'change_summary',
    ];

    protected $casts = [
        'geometry' => 'array',
        'stops_snapshot' => 'array',
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
