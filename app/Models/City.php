<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class City extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'state',
        'country',
        'latitude',
        'longitude',
        'zoom_level',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($city) {
            $slug = \Illuminate\Support\Str::slug($city->name);
            $originalSlug = $slug;
            $count = 1;
            while (static::where('slug', $slug)->exists()) {
                $slug = $originalSlug . '-' . $count++;
            }
            $city->slug = $slug;
        });
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function transitRoutes(): HasMany
    {
        return $this->hasMany(TransitRoute::class);
    }
}
