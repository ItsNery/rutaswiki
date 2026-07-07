<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\City;
use App\Models\TransitRoute;
use Illuminate\Support\Facades\Cache;

class AdminController extends Controller
{
    public function dashboard()
    {
        $stats = [
            'total_cities' => City::count(),
            'trashed_cities' => City::onlyTrashed()->count(),
            'total_routes' => TransitRoute::count(),
            'trashed_routes' => TransitRoute::onlyTrashed()->count(),
        ];

        return view('admin.dashboard', compact('stats'));
    }

    // ─── Cities ────────────────────────────────────────────

    public function cities()
    {
        $cities = City::withTrashed()->orderBy('name')->paginate(20);
        return view('admin.cities', compact('cities'));
    }

    public function deleteCity(City $city)
    {
        $city->delete();

        Cache::increment("api.city.{$city->id}.routes_version");

        return redirect()->route('admin.cities')
            ->with('success', "Ciudad '{$city->name}' eliminada (soft delete).");
    }

    public function restoreCity($id)
    {
        $city = City::withTrashed()->findOrFail($id);
        $city->restore();

        Cache::increment("api.city.{$city->id}.routes_version");

        return redirect()->route('admin.cities')
            ->with('success', "Ciudad '{$city->name}' restaurada.");
    }

    public function forceDeleteCity($id)
    {
        $city = City::withTrashed()->findOrFail($id);

        Cache::increment("api.city.{$city->id}.routes_version");

        $name = $city->name;
        $city->forceDelete();

        return redirect()->route('admin.cities')
            ->with('success', "Ciudad '{$name}' eliminada permanentemente.");
    }

    // ─── Routes ────────────────────────────────────────────

    public function routes()
    {
        $routes = TransitRoute::withTrashed()->with('city')->orderBy('name')->paginate(20);
        return view('admin.routes', compact('routes'));
    }

    public function deleteRoute(TransitRoute $route)
    {
        $route->delete();

        $this->invalidateRouteCache($route);

        return redirect()->route('admin.routes')
            ->with('success', "Ruta '{$route->name}' eliminada (soft delete).");
    }

    public function restoreRoute($id)
    {
        $route = TransitRoute::withTrashed()->findOrFail($id);
        $route->restore();

        $this->invalidateRouteCache($route);

        return redirect()->route('admin.routes')
            ->with('success', "Ruta '{$route->name}' restaurada.");
    }

    public function forceDeleteRoute($id)
    {
        $route = TransitRoute::withTrashed()->findOrFail($id);

        $name = $route->name;
        $this->invalidateRouteCache($route);
        $route->forceDelete();

        return redirect()->route('admin.routes')
            ->with('success', "Ruta '{$name}' eliminada permanentemente.");
    }

    private function invalidateRouteCache(TransitRoute $route): void
    {
        Cache::increment("api.city.{$route->city_id}.routes_version");
        Cache::forget("api.route.{$route->id}");
        Cache::forget('map.all_routes');
        Cache::forget("map.city.{$route->city_id}.nearby");
    }
}
