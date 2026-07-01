<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

use App\Models\City;

class CitySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        City::create([
            'name' => 'Tlaxco',
            'state' => 'Tlaxcala',
            'country' => 'México',
            'latitude' => 19.6139,
            'longitude' => -98.1200,
            'zoom_level' => 14,
        ]);

        City::create([
            'name' => 'Oaxaca de Juárez',
            'state' => 'Oaxaca',
            'country' => 'México',
            'latitude' => 17.0732,
            'longitude' => -96.7266,
            'zoom_level' => 13,
        ]);

        City::create([
            'name' => 'Toluca',
            'state' => 'Estado de México',
            'country' => 'México',
            'latitude' => 19.2826,
            'longitude' => -99.6557,
            'zoom_level' => 13,
        ]);
    }
}
