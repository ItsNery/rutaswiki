<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

use App\Models\City;
use App\Models\TransitRoute;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class TransitRouteSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $user = User::create([
            'name' => 'Vecino Colaborador',
            'email' => 'vecino@ejemplo.com',
            'password' => Hash::make('password'),
        ]);

        $tlaxco = City::where('name', 'Tlaxco')->first();

        if ($tlaxco) {
            $route1 = TransitRoute::create([
                'city_id' => $tlaxco->id,
                'user_id' => $user->id,
                'route_number' => 'R-01',
                'name' => 'Centro - Buena Vista (La Barca)',
                'description' => 'Esta ruta de combi sale de la plaza principal de Tlaxco rumbo a la Barca de la Fe en San Andrés Buenavista. Frecuencia de 20 minutos.',
                'transport_type' => 'combi',
                'geometry' => [
                    'type' => 'LineString',
                    'coordinates' => [
                        [-98.1197, 19.6141],
                        [-98.1300, 19.6000],
                        [-98.1500, 19.5850],
                        [-98.2000, 19.5750],
                        [-98.2758, 19.5686],
                    ]
                ],
                'color' => '#10b981',
                'status' => 'published',
                'vote_score' => 5,
                'revision_count' => 1,
            ]);

            $stopsData = [
                [
                    'name' => 'Plaza Principal Tlaxco',
                    'latitude' => 19.6141,
                    'longitude' => -98.1197,
                    'description' => 'Salida principal a un costado del kiosko.'
                ],
                [
                    'name' => 'Cruce de Carretera',
                    'latitude' => 19.5850,
                    'longitude' => -98.1500,
                    'description' => 'Punto de transbordo.'
                ],
                [
                    'name' => 'La Barca de la Fe',
                    'latitude' => 19.5686,
                    'longitude' => -98.2758,
                    'description' => 'Terminal enfrente del templo.'
                ]
            ];

            foreach ($stopsData as $index => $stopData) {
                $stop = \App\Models\Stop::firstOrCreate(
                    ['latitude' => $stopData['latitude'], 'longitude' => $stopData['longitude']],
                    ['name' => $stopData['name'], 'description' => $stopData['description']]
                );
                $route1->stops()->attach($stop->id, ['order' => $index + 1]);
            }

            $route1->comments()->create([
                'user_id' => $user->id,
                'body' => 'La combi pasa exactamente cada 20 minutos por la mañana. Por la tarde tarda un poco más, hasta 30 minutos.',
            ]);
        }
    }
}
