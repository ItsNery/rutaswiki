<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\City;
use App\Models\TransitRoute;
use App\Models\RouteRevision;
use App\Models\Stop;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class TransitRouteController extends Controller
{
    public function create(City $city)
    {
        return view('routes.create', compact('city'));
    }

    public function store(Request $request, City $city)
    {
        if ($request->has('geometry') && is_string($request->input('geometry'))) {
            $request->merge([
                'geometry' => json_decode($request->input('geometry'), true)
            ]);
        }

        $validated = $request->validate([
            'route_number' => 'nullable|string|max:50',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'transport_type' => 'required|string|in:bus,combi,metro,tram,trolley,other',
            'geometry' => 'required|array', // Expecting GeoJSON geometry structure
            'color' => 'required|string|max:7', // Hex color e.g., #ff0000
            'stops' => 'nullable|array',
            'stops.*.name' => 'required|string|max:255',
            'stops.*.latitude' => 'required|numeric',
            'stops.*.longitude' => 'required|numeric',
            'stops.*.description' => 'nullable|string',
            'schedules' => 'required|array|min:1',
            'schedules.*.day_type' => 'required|string|in:weekday,saturday,sunday,holiday',
            'schedules.*.start_time' => 'required|string|regex:/^\d{2}:\d{2}$/',
            'schedules.*.end_time' => 'required|string|regex:/^\d{2}:\d{2}$/',
            'schedules.*.frequency_minutes' => 'required|integer|min:1',
        ]);

        DB::transaction(function () use ($validated, $city) {
            $route = TransitRoute::create([
                'city_id' => $city->id,
                'user_id' => Auth::id(),
                'route_number' => $validated['route_number'],
                'name' => $validated['name'],
                'description' => $validated['description'],
                'transport_type' => $validated['transport_type'],
                'geometry' => $validated['geometry'],
                'color' => $validated['color'],
                'status' => 'published',
            ]);

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

        return redirect()->route('cities.show', $city)
            ->with('success', 'Ruta creada exitosamente.');
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

        return view('routes.edit', compact('city', 'route'));
    }

    public function update(Request $request, City $city, TransitRoute $route)
    {
        abort_unless($route->city_id === $city->id, 404);

        if ($request->has('geometry') && is_string($request->input('geometry'))) {
            $request->merge([
                'geometry' => json_decode($request->input('geometry'), true)
            ]);
        }

        $validated = $request->validate([
            'route_number' => 'nullable|string|max:50',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'transport_type' => 'required|string|in:bus,combi,metro,tram,trolley,other',
            'geometry' => 'required|array',
            'color' => 'required|string|max:7',
            'change_summary' => 'required|string|max:255',
            'stops' => 'nullable|array',
            'stops.*.name' => 'required|string|max:255',
            'stops.*.latitude' => 'required|numeric',
            'stops.*.longitude' => 'required|numeric',
            'stops.*.description' => 'nullable|string',
            'schedules' => 'required|array|min:1',
            'schedules.*.day_type' => 'required|string|in:weekday,saturday,sunday,holiday',
            'schedules.*.start_time' => 'required|string|regex:/^\d{2}:\d{2}$/',
            'schedules.*.end_time' => 'required|string|regex:/^\d{2}:\d{2}$/',
            'schedules.*.frequency_minutes' => 'required|integer|min:1',
        ]);

        DB::transaction(function () use ($validated, $route) {
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
                'stops_snapshot' => $previousStops,
                'change_summary' => $validated['change_summary'],
            ]);

            $route->update([
                'route_number' => $validated['route_number'],
                'name' => $validated['name'],
                'description' => $validated['description'],
                'transport_type' => $validated['transport_type'],
                'geometry' => $validated['geometry'],
                'color' => $validated['color'],
                'revision_count' => $route->revision_count + 1,
            ]);

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

        return redirect()->route('routes.show', [$city, $route])
            ->with('success', 'Ruta actualizada exitosamente.');
    }

    public function history(City $city, TransitRoute $route)
    {
        abort_unless($route->city_id === $city->id, 404);

        $revisions = $route->revisions()->with('user')->orderBy('created_at', 'desc')->paginate(15);
 
        return view('routes.history', compact('city', 'route', 'revisions'));
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
