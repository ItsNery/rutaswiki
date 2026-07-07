<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\TransitRoute;

class SeedCityRoute extends Command
{
    protected $signature = 'app:seed-city-route';
    protected $description = 'Populate city_route pivot with existing routes primary city';

    public function handle()
    {
        $routes = TransitRoute::all();

        foreach ($routes as $route) {
            $route->cities()->syncWithoutDetaching([$route->city_id]);
        }

        $this->info("city_route pivot populated for {$routes->count()} routes.");

        return Command::SUCCESS;
    }
}
