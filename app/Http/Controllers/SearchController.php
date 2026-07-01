<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\TransitRoute;
use App\Models\City;

class SearchController extends Controller
{
    public function index(Request $request)
    {
        $q = $request->input('q');

        if (blank($q)) {
            return redirect()->route('cities.index');
        }

        $routes = TransitRoute::with('city')
            ->where('status', 'published')
            ->where(function ($query) use ($q) {
                $query->where('name', 'like', "%{$q}%")
                    ->orWhere('route_number', 'like', "%{$q}%");
            })
            ->orderBy('vote_score', 'desc')
            ->paginate(20);

        $cities = City::withCount('transitRoutes')
            ->where(function ($query) use ($q) {
                $query->where('name', 'like', "%{$q}%")
                    ->orWhere('state', 'like', "%{$q}%");
            })
            ->orderBy('name')
            ->get();

        $totalResults = $routes->total() + $cities->count();

        return view('search.index', compact('q', 'routes', 'cities', 'totalResults'));
    }
}
