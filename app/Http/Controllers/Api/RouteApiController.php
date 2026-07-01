<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\City;
use App\Models\TransitRoute;

class RouteApiController extends Controller
{
    public function index(City $city)
    {
        // 1. Get routes belonging directly to this city
        $cityRoutes = $city->transitRoutes()
            ->with(['stops', 'city'])
            ->where('status', 'published')
            ->get();

        // 2. Get all other published routes (from other cities) to check proximity
        $otherRoutes = TransitRoute::with(['stops', 'city'])
            ->where('city_id', '!=', $city->id)
            ->where('status', 'published')
            ->get();

        // Filter other routes that pass near the city center (within 15 km)
        $nearbyOtherRoutes = $otherRoutes->filter(function ($route) use ($city) {
            return $route->passesNear($city->latitude, $city->longitude, 15);
        });

        // 3. Combine them
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
        $radius = (float) $request->input('radius', 5); // default to 5 km

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
    }

    public function show(TransitRoute $route)
    {
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
    }
}
