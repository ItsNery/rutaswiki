<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

use App\Models\City;
use App\Models\TransitRoute;

class RouteApiController extends Controller
{
    public function index(City $city, Request $request)
    {
        $version = Cache::remember("api.city.{$city->id}.routes_version", 3600, fn() => 1);

        // When ?all=1, return everything without pagination (used by global map)
        if ($request->boolean('all')) {
            return Cache::remember("api.city.{$city->id}.routes.v{$version}.all", 600, function () use ($city) {
                return $this->buildResponse($city, null, null);
            });
        }

        $perPage = min((int) $request->input('per_page', 50), 100);
        $page = (int) $request->input('page', 1);

        $cacheKey = "api.city.{$city->id}.routes.v{$version}.page.{$page}.per.{$perPage}";

        return Cache::remember($cacheKey, 600, function () use ($city, $perPage, $page) {
            return $this->buildResponse($city, $perPage, $page);
        });
    }

    private function buildResponse(City $city, ?int $perPage, ?int $page): \Illuminate\Http\JsonResponse
    {
        // Primary routes (city_id matches)
        $primaryQuery = $city->transitRoutes()
            ->with(['city'])
            ->where('status', 'published')
            ->orderBy('vote_score', 'desc');

        // Additional routes (via city_route pivot)
        $additionalQuery = TransitRoute::whereHas('cities', fn($q) => $q->where('city_id', $city->id))
            ->where('city_id', '!=', $city->id)
            ->where('status', 'published')
            ->orderBy('vote_score', 'desc');

        $primaryTotal = $primaryQuery->count();
        $additionalTotal = $additionalQuery->count();
        $total = $primaryTotal + $additionalTotal;

        if ($perPage && $page) {
            $primaryRoutes = $primaryQuery->skip(($page - 1) * $perPage)->take($perPage)->get();
            $remaining = $perPage - $primaryRoutes->count();
            $additionalRoutes = $remaining > 0
                ? $additionalQuery->skip(0)->take($remaining)->get()
                : collect();
        } else {
            $primaryRoutes = $primaryQuery->get();
            $additionalRoutes = $additionalQuery->get();
        }

        $allRoutes = $primaryRoutes->concat($additionalRoutes);

        $features = $allRoutes->map(function ($route) use ($city) {
            $isPrimary = $route->city_id === $city->id;
            $feature = [
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
                    'is_additional' => !$isPrimary,
                    'round_trip' => $route->round_trip ?? false,
                    'has_designated_stops' => $route->has_designated_stops ?? false,
                    'geometry_return' => $route->geometry_return,
                ],
                'geometry' => $route->geometry,
            ];
            return $feature;
        });

        // Add return geometry features for round trip routes
        $returnFeatures = $primaryRoutes->filter(fn($r) => $r->round_trip && $r->geometry_return)
            ->map(function ($route) {
                return [
                    'type' => 'Feature',
                    'id' => $route->id . '-return',
                    'properties' => [
                        'route_number' => $route->route_number,
                        'name' => $route->name . ' (vuelta)',
                        'transport_type' => $route->transport_type,
                        'color' => $route->color,
                        'vote_score' => $route->vote_score,
                        'description' => $route->description,
                        'url' => route('routes.show', [$route->city, $route]),
                        'is_additional' => false,
                        'round_trip' => true,
                        'is_return' => true,
                        'has_designated_stops' => $route->has_designated_stops ?? false,
                    ],
                    'geometry' => $route->geometry_return,
                ];
            });

        $lastPage = $perPage ? (int) ceil(max($primaryTotal, 1) / $perPage) : 1;

        $response = [
            'type' => 'FeatureCollection',
            'features' => $features->values()->merge($returnFeatures)->values(),
            'additional_count' => $additionalTotal,
        ];

        if ($perPage !== null) {
            $response['pagination'] = [
                'current_page' => $page ?? 1,
                'last_page' => $lastPage,
                'per_page' => $perPage,
                'total' => $total,
                'has_more' => ($page ?? 1) < $lastPage,
            ];
        }

        return response()->json($response);
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
                        'round_trip' => $route->round_trip ?? false,
                        'geometry_return' => $route->geometry_return,
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
                'geometry_return' => $route->geometry_return,
                'round_trip' => $route->round_trip ?? false,
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

    public function suggestPlaces(Request $request)
    {
        $request->validate([
            'coordinates' => 'required|array|min:2',
            'coordinates.*' => 'required|array|size:2',
            'coordinates.*.0' => 'required|numeric|between:-180,180',
            'coordinates.*.1' => 'required|numeric|between:-90,90',
        ]);

        $coords = $request->input('coordinates');
        $buffer = 0.05;

        $lngs = array_column($coords, 0);
        $lats = array_column($coords, 1);
        $minLng = min($lngs) - $buffer;
        $maxLng = max($lngs) + $buffer;
        $minLat = min($lats) - $buffer;
        $maxLat = max($lats) + $buffer;

        $query = "[out:json];
            node[\"place\"]({$minLat},{$minLng},{$maxLat},{$maxLng});
            out center;";

        try {
            $context = stream_context_create([
                'http' => [
                    'method' => 'POST',
                    'header' => "Content-Type: text/plain\r\nUser-Agent: RutasWiki/1.0\r\nAccept: application/json\r\n",
                    'content' => $query,
                    'timeout' => 10,
                ],
                'ssl' => ['verify_peer' => false, 'verify_peer_name' => false],
            ]);
            $result = @file_get_contents('https://overpass-api.de/api/interpreter', false, $context);
            if ($result === false) {
                return response()->json(['error' => 'Overpass API unreachable'], 502);
            }
            $data = json_decode($result, true);
            $elements = $data['elements'] ?? [];
        } catch (\Throwable $e) {
            return response()->json(['error' => 'Overpass API error: ' . $e->getMessage()], 502);
        }

        $existingNames = City::pluck('name')
            ->map(fn($n) => mb_strtolower(trim($n)))->unique()->values()->toArray();

        $results = [];
        foreach ($elements as $el) {
            $lat = $el['lat'] ?? null;
            $lng = $el['lon'] ?? null;
            if (!$lat || !$lng) continue;

            $name = $el['tags']['name'] ?? null;
            if (!$name) continue;

            if (in_array(mb_strtolower(trim($name)), $existingNames)) continue;

            $minDist = PHP_FLOAT_MAX;
            foreach ($coords as $c) {
                $d = TransitRoute::haversineDistance($lat, $lng, $c[1], $c[0]);
                if ($d < $minDist) $minDist = $d;
            }

            if ($minDist <= 5) {
                $results[] = [
                    'name' => $name,
                    'latitude' => $lat,
                    'longitude' => $lng,
                    'distance_km' => round($minDist, 2),
                    'place_type' => $el['tags']['place'] ?? 'unknown',
                ];
            }
        }

        usort($results, fn($a, $b) => $a['distance_km'] <=> $b['distance_km']);

        return response()->json(array_slice($results, 0, 15));
    }
}
