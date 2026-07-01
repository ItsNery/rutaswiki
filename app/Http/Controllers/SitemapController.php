<?php

namespace App\Http\Controllers;

use App\Models\City;
use App\Models\TransitRoute;

class SitemapController extends Controller
{
    public function index()
    {
        $cities = City::orderBy('name')->get(['slug', 'updated_at']);
        $routes = TransitRoute::with('city')
            ->where('status', 'published')
            ->orderBy('name')
            ->get(['slug', 'city_id', 'updated_at']);

        return response()->view('sitemap', compact('cities', 'routes'))
            ->header('Content-Type', 'application/xml');
    }
}
