<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\City;

class CityController extends Controller
{
    public function home()
    {
        $featuredCities = City::withCount('transitRoutes')
            ->orderBy('transit_routes_count', 'desc')
            ->take(6)
            ->get();

        return view('home', compact('featuredCities'));
    }

    public function index(Request $request)
    {
        $search = $request->input('search');

        $cities = City::withCount('transitRoutes')
            ->when($search, function ($query, $search) {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('state', 'like', "%{$search}%")
                    ->orWhere('country', 'like', "%{$search}%");
            })
            ->orderBy('name')
            ->paginate(12);

        return view('cities.index', compact('cities', 'search'));
    }

    public function show(City $city)
    {
        $city->load(['transitRoutes' => function ($query) {
            $query->withCount('comments')->with('user');
        }]);

        return view('cities.show', compact('city'));
    }

    public function create()
    {
        return view('cities.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'state' => 'required|string|max:255',
            'country' => 'required|string|max:255',
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'zoom_level' => 'required|integer|between:5,18',
        ]);

        $exists = City::where('name', $validated['name'])
            ->where('state', $validated['state'])
            ->where('country', $validated['country'])
            ->exists();

        if ($exists) {
            return back()->withInput()->withErrors([
                'name' => 'Esta ciudad ya está registrada en el estado y país seleccionados.'
            ]);
        }

        $city = City::create($validated);

        return redirect()->route('cities.show', $city)
            ->with('success', 'Ciudad registrada exitosamente. ¡Ya puedes comenzar a agregar rutas!');
    }
}
