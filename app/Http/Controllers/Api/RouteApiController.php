<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

use App\Models\City;
use App\Models\TransitRoute;

class RouteApiController extends Controller
{
    public function index(City $city)
    {
        return Cache::remember("api.city.{$city->id}.routes", 600, function () use ($city) {
            $cityRoutes = $city->transitRoutes()
                ->with(['stops', 'city'])
                ->where('status', 'published')
                ->get();

            $otherRoutes = TransitRoute::with(['stops', 'city'])
                ->where('city_id', '!=', $city->id)
                ->where('status', 'published')
                ->get();

            $nearbyOtherRoutes = $otherRoutes->filter(function ($route) use ($city) {
                return $route->passesNear($city->latitude, $city->longitude, 10);
            });

            $routes = $cityRoutes->concat($nearbyOtherRoutes);

            $features = $routes->map(function ($route) {
                return [
                    'type' => 'Feature',
                    'id' => $route->id,
                    'properties' => [
                        'route_number' => $route->route_number,
                        'name' => $route->name,
                        'transport_type' => $route->transport_type,
                        'color' => $route->color,
                        'vote_score' => $route->vote_score,
                        'description' => $route->description,
                        'url' => route('routes.show', [$route->city, $route]),
                    ],
                    'geometry' => $route->geometry,
                ];
            });

            return response()->json([
                'type' => 'FeatureCollection',
                'features' => $features->values(),
            ]);
        });
    }

    public function search(Request $request)
    {
        $request->validate(['q' => 'required|string|max:100']);

        $q = $request->input('q');

        $routes = TransitRoute::with('city')
            ->where('status', 'published')
            ->where(function ($query) use ($q) {
                $query->where('name', 'like', "%{$q}%")
                    ->orWhere('route_number', 'like', "%{$q}%");
            })
            ->orderBy('vote_score', 'desc')
            ->take(10)
            ->get();

        return response()->json($routes->map(function ($route) {
            return [
                'id' => $route->id,
                'slug' => $route->slug,
                'route_number' => $route->route_number,
                'name' => $route->name,
                'transport_type' => $route->transport_type,
                'color' => $route->color,
                'vote_score' => $route->vote_score,
                'city' => ['name' => $route->city->name, 'slug' => $route->city->slug],
                'url' => route('routes.show', [$route->city, $route]),
            ];
        }));
    }

    public function nearby(Request $request)
    {
        $request->validate([
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'radius' => 'nullable|numeric|min:0.1|max:100',
        ]);

        $lat = (float) $request->input('latitude');
        $lng = (float) $request->input('longitude');
        $radius = (float) $request->input('radius', 5);

        $cacheKey = 'api.nearby.' . (int)($lat * 10) . '.' . (int)($lng * 10) . '.' . (int)$radius;

        return Cache::remember($cacheKey, 300, function () use ($lat, $lng, $radius) {
            $routes = TransitRoute::with(['stops', 'city'])
                ->where('status', 'published')
                ->get();

            $nearbyRoutes = $routes->filter(function ($route) use ($lat, $lng, $radius) {
                return $route->passesNear($lat, $lng, $radius);
            });

            $features = $nearbyRoutes->map(function ($route) {
                return [
                    'type' => 'Feature',
                    'id' => $route->id,
                    'properties' => [
                        'route_number' => $route->route_number,
                        'name' => $route->name,
                        'transport_type' => $route->transport_type,
                        'color' => $route->color,
                        'vote_score' => $route->vote_score,
                        'description' => $route->description,
                        'url' => route('routes.show', [$route->city, $route]),
                    ],
                    'geometry' => $route->geometry,
                ];
            });

            return response()->json([
                'type' => 'FeatureCollection',
                'features' => $features->values(),
            ]);
        });
    }

    public function show(TransitRoute $route)
    {
        return Cache::remember("api.route.{$route->id}", 600, function () use ($route) {
            $route->load('stops');

            return response()->json([
                'id' => $route->id,
                'route_number' => $route->route_number,
                'name' => $route->name,
                'description' => $route->description,
                'transport_type' => $route->transport_type,
                'geometry' => $route->geometry,
                'color' => $route->color,
                'stops' => $route->stops->map(function ($stop) {
                    return [
                        'id' => $stop->id,
                        'name' => $stop->name,
                        'latitude' => $stop->latitude,
                        'longitude' => $stop->longitude,
                        'order' => $stop->order,
                        'description' => $stop->description,
                    ];
                }),
            ]);
        });
    }
}
