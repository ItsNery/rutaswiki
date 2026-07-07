<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class City extends Model
{
    use SoftDeletes;
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

    public function additionalRoutes(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(TransitRoute::class, 'city_route')
            ->withTimestamps();
    }

    public function allRoutes(): \Illuminate\Database\Eloquent\Collection
    {
        return $this->transitRoutes->merge($this->additionalRoutes);
    }
}
