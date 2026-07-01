<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TransitRoute extends Model
{
    protected $fillable = [
        'city_id',
        'user_id',
        'route_number',
        'name',
        'slug',
        'description',
        'transport_type',
        'geometry',
        'color',
        'status',
        'vote_score',
        'revision_count',
    ];

    protected $casts = [
        'geometry' => 'array',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($route) {
            $slug = \Illuminate\Support\Str::slug($route->name);
            if (empty($slug)) {
                $slug = 'ruta';
            }
            $originalSlug = $slug;
            $count = 1;
            while (static::where('slug', $slug)->exists()) {
                $slug = $originalSlug . '-' . $count++;
            }
            $route->slug = $slug;
        });
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function city(): BelongsTo
    {
        return $this->belongsTo(City::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function schedules(): HasMany
    {
        return $this->hasMany(RouteSchedule::class);
    }

    public function stops(): HasMany
    {
        return $this->hasMany(Stop::class)->orderBy('order', 'asc');
    }

    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class)->orderBy('created_at', 'desc');
    }

    public function votes(): HasMany
    {
        return $this->hasMany(Vote::class);
    }

    public function revisions(): HasMany
    {
        return $this->hasMany(RouteRevision::class)->orderBy('created_at', 'desc');
    }

    /**
     * Calculates the distance in km between two coordinates using Haversine formula.
     */
    public static function haversineDistance($lat1, $lon1, $lat2, $lon2): float
    {
        $earthRadius = 6371; // in km

        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);

        $a = sin($dLat / 2) * sin($dLat / 2) +
             cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
             sin($dLon / 2) * sin($dLon / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadius * $c;
    }

    /**
     * Checks if this route passes within X kilometers of a coordinate.
     */
    public function passesNear($lat, $lng, $radiusKm = 15): bool
    {
        if (empty($this->geometry) || !isset($this->geometry['coordinates'])) {
            return false;
        }

        foreach ($this->geometry['coordinates'] as $coord) {
            // GeoJSON coordinates are [lng, lat]
            $coordLng = $coord[0];
            $coordLat = $coord[1];

            $dist = self::haversineDistance($lat, $lng, $coordLat, $coordLng);
            if ($dist <= $radiusKm) {
                return true;
            }
        }

        // Also check if any stops are near
        foreach ($this->stops as $stop) {
            $dist = self::haversineDistance($lat, $lng, $stop->latitude, $stop->longitude);
            if ($dist <= $radiusKm) {
                return true;
            }
        }

        return false;
    }
}
