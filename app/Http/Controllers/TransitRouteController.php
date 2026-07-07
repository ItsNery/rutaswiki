<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\City;
use App\Models\TransitRoute;
use App\Models\RouteRevision;
use App\Models\Stop;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use App\Http\Requests\StoreTransitRouteRequest;
use App\Http\Requests\UpdateTransitRouteRequest;

class TransitRouteController extends Controller
{
    public function create(City $city)
    {
        $cities = \App\Models\City::orderBy('name')->get(['id', 'name', 'latitude', 'longitude']);
        return view('routes.create', compact('city', 'cities'));
    }

    private function resolveCityIds(array $values): array
    {
        $ids = [];
        foreach ($values as $value) {
            if (is_numeric($value)) {
                $ids[] = (int) $value;
            } else {
                $name = trim($value);
                if ($name === '') continue;

                $existing = City::where('name', $name)->first();
                if ($existing) {
                    $ids[] = $existing->id;
                    continue;
                }

                $city = $this->geocodeAndCreateCity($name);
                if ($city) {
                    $ids[] = $city->id;
                }
            }
        }
        return $ids;
    }

    private function geocodeAndCreateCity(string $name): ?City
    {
        $url = 'https://nominatim.openstreetmap.org/search?q=' . urlencode($name) . '&format=json&addressdetails=1&limit=1&accept-language=es';

        try {
            $context = stream_context_create([
                'http' => [
                    'method' => 'GET',
                    'header' => "User-Agent: RutasWiki/1.0\r\nAccept: application/json\r\n",
                    'timeout' => 5,
                ],
                'ssl' => ['verify_peer' => false, 'verify_peer_name' => false],
            ]);

            $result = @file_get_contents($url, false, $context);
            if ($result === false) return null;

            $data = json_decode($result, true);
            if (empty($data)) return null;

            $place = $data[0];
            $address = $place['address'] ?? [];

            $state = $address['state'] ?? $address['region'] ?? '';
            $country = $address['country'] ?? '';
            $lat = (float) $place['lat'];
            $lng = (float) $place['lon'];

            return City::create([
                'name' => $name,
                'state' => $state,
                'country' => $country,
                'latitude' => $lat,
                'longitude' => $lng,
                'zoom_level' => 12,
            ]);
        } catch (\Throwable) {
            return null;
        }
    }

    public function store(StoreTransitRouteRequest $request, City $city)
    {
        $validated = $request->validated();

        DB::transaction(function () use ($validated, $city) {
            $route = TransitRoute::create([
                'city_id' => $city->id,
                'user_id' => Auth::id(),
                'route_number' => $validated['route_number'],
                'name' => $validated['name'],
                'description' => $validated['description'],
                'transport_type' => $validated['transport_type'],
                'geometry' => $validated['geometry'],
                'geometry_return' => $validated['geometry_return'] ?? null,
                'round_trip' => $validated['round_trip'] ?? false,
                'has_designated_stops' => $validated['has_designated_stops'] ?? false,
                'color' => $validated['color'],
                'status' => 'published',
            ]);

            // Sync city associations: primary city + additional cities
            $additional = $this->resolveCityIds($validated['additional_cities'] ?? []);
            $cityIds = array_merge([$city->id], $additional);
            $route->cities()->sync($cityIds);

            if (!empty($validated['schedules'])) {
                foreach ($validated['schedules'] as $scheduleData) {
                    $route->schedules()->create($scheduleData);
                }
            }

            if (!empty($validated['stops'])) {
                foreach ($validated['stops'] as $index => $stopData) {
                    $route->stops()->create([
                        'name' => $stopData['name'],
                        'latitude' => $stopData['latitude'],
                        'longitude' => $stopData['longitude'],
                        'order' => $index + 1,
                        'description' => $stopData['description'] ?? null,
                    ]);
                }
            }
        });

        $route = $city->transitRoutes()->latest()->first();
        if ($route) {
            $this->clearRouteCache($route);
        }

        return redirect()->route('cities.show', $city)
            ->with('success', 'Ruta creada exitosamente.');
    }

    private function clearRouteCache(TransitRoute $route): void
    {
        // Invalidate all associated cities (primary + additional via pivot)
        $cityIds = $route->cities()->pluck('cities.id')->push($route->city_id)->unique()->values()->all();
        foreach ($cityIds as $cid) {
            Cache::increment("api.city.{$cid}.routes_version");
            Cache::forget("map.city.{$cid}.nearby");
        }
        Cache::forget("api.route.{$route->id}");
        Cache::forget('map.all_routes');
    }

    public function show(City $city, TransitRoute $route)
    {
        abort_unless($route->city_id === $city->id, 404);

        $route->load(['stops', 'comments.user', 'revisions.user', 'user', 'schedules']);
        
        $userVote = Auth::check() ? $route->votes()->where('user_id', Auth::id())->first()?->value : null;

        return view('routes.show', compact('city', 'route', 'userVote'));
    }

    public function edit(City $city, TransitRoute $route)
    {
        abort_unless($route->city_id === $city->id, 404);

        $route->load(['stops', 'schedules']);

        $cities = \App\Models\City::orderBy('name')->get(['id', 'name', 'latitude', 'longitude']);
        $additionalCityIds = $route->cities()->where('city_id', '!=', $city->id)->pluck('city_id')->toArray();

        return view('routes.edit', compact('city', 'route', 'cities', 'additionalCityIds'));
    }

    public function update(UpdateTransitRouteRequest $request, City $city, TransitRoute $route)
    {
        abort_unless($route->city_id === $city->id, 404);

        $validated = $request->validated();

        DB::transaction(function () use ($validated, $route, $city) {
            $previousStops = $route->stops->map(function ($stop) {
                return [
                    'name' => $stop->name,
                    'latitude' => $stop->latitude,
                    'longitude' => $stop->longitude,
                    'order' => $stop->order,
                    'description' => $stop->description,
                ];
            })->toArray();

            RouteRevision::create([
                'transit_route_id' => $route->id,
                'user_id' => Auth::id(),
                'geometry' => $route->geometry,
                'geometry_return' => $route->geometry_return,
                'stops_snapshot' => $previousStops,
                'change_summary' => $validated['change_summary'],
            ]);

            $route->update([
                'route_number' => $validated['route_number'],
                'name' => $validated['name'],
                'description' => $validated['description'],
                'transport_type' => $validated['transport_type'],
                'geometry' => $validated['geometry'],
                'geometry_return' => $validated['geometry_return'] ?? null,
                'round_trip' => $validated['round_trip'] ?? false,
                'has_designated_stops' => $validated['has_designated_stops'] ?? false,
                'color' => $validated['color'],
                'revision_count' => $route->revision_count + 1,
            ]);

            // Sync city associations
            $additional = $this->resolveCityIds($validated['additional_cities'] ?? []);
            $cityIds = array_merge([$city->id], $additional);
            $route->cities()->sync($cityIds);

            $route->schedules()->delete();
            if (!empty($validated['schedules'])) {
                foreach ($validated['schedules'] as $scheduleData) {
                    $route->schedules()->create($scheduleData);
                }
            }

            $route->stops()->delete();
            if (!empty($validated['stops'])) {
                foreach ($validated['stops'] as $index => $stopData) {
                    $route->stops()->create([
                        'name' => $stopData['name'],
                        'latitude' => $stopData['latitude'],
                        'longitude' => $stopData['longitude'],
                        'order' => $index + 1,
                        'description' => $stopData['description'] ?? null,
                    ]);
                }
            }
        });

        $this->clearRouteCache($route);

        return redirect()->route('routes.show', [$city, $route])
            ->with('success', 'Ruta actualizada exitosamente.');
    }

    public function history(City $city, TransitRoute $route)
    {
        abort_unless($route->city_id === $city->id, 404);

        $revisions = $route->revisions()->with('user')->orderBy('created_at', 'desc')->paginate(15);
 
        return view('routes.history', compact('city', 'route', 'revisions'));
    }

    public function diff(City $city, TransitRoute $route, RouteRevision $revision, Request $request)
    {
        abort_unless($route->city_id === $city->id, 404);
        abort_unless($revision->transit_route_id === $route->id, 404);

        $route->load(['stops', 'city', 'user']);

        // Support ?against=current to compare against current route state
        // or ?against=REVISION_ID to compare against a specific revision
        $against = $request->input('against');

        if ($against === 'current') {
            // Compare revision against the current published route
            $previousRevision = null;
            $oldGeometry = $revision->geometry;
            $oldGeometryReturn = $revision->geometry_return;
            $oldStops = $revision->stops_snapshot ?? [];
            $oldRoundTrip = $revision->round_trip ?? false;

            $newGeometry = $route->geometry;
            $newGeometryReturn = $route->geometry_return;
            $newStops = $route->stops->map(function ($stop) {
                return ['name' => $stop->name, 'latitude' => $stop->latitude, 'longitude' => $stop->longitude, 'order' => $stop->order, 'description' => $stop->description];
            })->toArray();
            $newRoundTrip = $route->round_trip ?? false;

            $comparingWithCurrent = true;
        } else {
            $againstId = (int) $against;
            if ($againstId) {
                $previousRevision = RouteRevision::where('id', $againstId)
                    ->where('transit_route_id', $route->id)
                    ->first();
            } else {
                // Default: find the previous revision immediately before this one
                $previousRevision = RouteRevision::where('transit_route_id', $route->id)
                    ->where('created_at', '<', $revision->created_at)
                    ->orderBy('created_at', 'desc')
                    ->first();
            }

            $oldGeometry = $previousRevision?->geometry;
            $oldGeometryReturn = $previousRevision?->geometry_return;
            $oldStops = $previousRevision?->stops_snapshot ?? [];
            $oldRoundTrip = $previousRevision?->round_trip ?? false;

            $newGeometry = $revision->geometry;
            $newGeometryReturn = $revision->geometry_return;
            $newStops = $revision->stops_snapshot ?? [];
            $newRoundTrip = $revision->round_trip ?? false;

            $comparingWithCurrent = false;
        }

        // Calculate stop diff
        $oldStopNames = collect($oldStops)->pluck('name')->values();
        $newStopNames = collect($newStops)->pluck('name')->values();

        $addedStops = $newStopNames->diff($oldStopNames)->values();
        $removedStops = $oldStopNames->diff($newStopNames)->values();

        // Build combined stop list for table view
        $stopDiff = [];
        $maxStops = max(count($oldStops), count($newStops));
        for ($i = 0; $i < $maxStops; $i++) {
            $oldStop = $oldStops[$i] ?? null;
            $newStop = $newStops[$i] ?? null;
            $status = 'unchanged';
            if (!$oldStop) $status = 'added';
            elseif (!$newStop) $status = 'removed';
            elseif ($oldStop['name'] !== $newStop['name'] || 
                    $oldStop['latitude'] != $newStop['latitude'] || 
                    $oldStop['longitude'] != $newStop['longitude']) {
                $status = 'modified';
            }
            $stopDiff[] = [
                'order' => $i + 1,
                'old' => $oldStop,
                'new' => $newStop,
                'status' => $status,
            ];
        }

        return view('routes.diff', compact(
            'city', 'route', 'revision', 'previousRevision',
            'oldGeometry', 'newGeometry',
            'oldGeometryReturn', 'newGeometryReturn',
            'oldRoundTrip', 'newRoundTrip',
            'oldStops', 'newStops',
            'addedStops', 'removedStops',
            'stopDiff', 'comparingWithCurrent'
        ));
    }

    public function calculateSchedule(Request $request)
    {
        $start = $request->input('start_time');
        $end = $request->input('end_time');
        $frequency = (int) $request->input('frequency');

        if (!$start || !$end || $frequency <= 0) {
            return response()->json(['times' => []]);
        }

        try {
            $startTime = \Carbon\Carbon::createFromFormat('H:i', $start);
            $endTime = \Carbon\Carbon::createFromFormat('H:i', $end);
        } catch (\Exception $e) {
            return response()->json(['times' => []]);
        }

        if ($endTime->lessThanOrEqualTo($startTime)) {
            $endTime->addDay(); // Handle overnight routes
        }

        $times = [];
        $current = $startTime->copy();
        $maxRuns = 300; // safety ceiling to prevent infinite loops

        while ($current->lessThanOrEqualTo($endTime) && $maxRuns > 0) {
            $times[] = $current->format('H:i');
            $current->addMinutes($frequency);
            $maxRuns--;
        }

        return response()->json(['times' => $times]);
    }
}
