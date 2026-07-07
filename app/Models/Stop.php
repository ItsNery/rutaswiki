<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Stop extends Model
{
    protected $fillable = [
        'name',
        'latitude',
        'longitude',
        'description',
    ];

    public function transitRoutes(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(TransitRoute::class, 'route_stop')
            ->withPivot('order')
            ->withTimestamps();
    }
}
