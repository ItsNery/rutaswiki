<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Create the route_stop pivot table
        Schema::create('route_stop', function (Blueprint $table) {
            $table->id();
            $table->foreignId('transit_route_id')->constrained()->cascadeOnDelete();
            $table->foreignId('stop_id')->constrained()->cascadeOnDelete();
            $table->integer('order');
            $table->timestamps();
        });

        // 2. Migrate and deduplicate existing stops
        $oldStops = DB::table('stops')->get();
        $uniqueStops = [];

        foreach ($oldStops as $stop) {
            // Group stops by name and coordinates (rounded slightly to handle float/decimal inaccuracies)
            $lat = round((float) $stop->latitude, 6);
            $lng = round((float) $stop->longitude, 6);
            $key = strtolower(trim($stop->name)) . "_{$lat}_{$lng}";

            if (isset($uniqueStops[$key])) {
                $canonicalStopId = $uniqueStops[$key];

                // Associate the route with the existing stop
                DB::table('route_stop')->insert([
                    'transit_route_id' => $stop->transit_route_id,
                    'stop_id' => $canonicalStopId,
                    'order' => $stop->order,
                    'created_at' => $stop->created_at ?? now(),
                    'updated_at' => $stop->updated_at ?? now(),
                ]);

                // Delete the duplicate stop record
                DB::table('stops')->where('id', $stop->id)->delete();
            } else {
                $uniqueStops[$key] = $stop->id;

                // Associate the route with this stop
                DB::table('route_stop')->insert([
                    'transit_route_id' => $stop->transit_route_id,
                    'stop_id' => $stop->id,
                    'order' => $stop->order,
                    'created_at' => $stop->created_at ?? now(),
                    'updated_at' => $stop->updated_at ?? now(),
                ]);
            }
        }

        // 3. Remove transit_route_id and order columns from stops table
        Schema::table('stops', function (Blueprint $table) {
            $table->dropForeign(['transit_route_id']);
            $table->dropColumn(['transit_route_id', 'order']);
        });
    }

    public function down(): void
    {
        // 1. Add columns back to stops table
        Schema::table('stops', function (Blueprint $table) {
            $table->unsignedBigInteger('transit_route_id')->nullable();
            $table->integer('order')->nullable();
        });

        // 2. Restore relationships and duplicate stops as they were before
        $pivotRelations = DB::table('route_stop')->get();
        foreach ($pivotRelations as $relation) {
            $stop = DB::table('stops')->where('id', $relation->stop_id)->first();
            if ($stop) {
                if ($stop->transit_route_id === null) {
                    // Update the existing stop with its first route relationship
                    DB::table('stops')->where('id', $stop->id)->update([
                        'transit_route_id' => $relation->transit_route_id,
                        'order' => $relation->order,
                    ]);
                } else {
                    // The stop is shared, so we duplicate it for the other route
                    DB::table('stops')->insert([
                        'name' => $stop->name,
                        'latitude' => $stop->latitude,
                        'longitude' => $stop->longitude,
                        'description' => $stop->description,
                        'transit_route_id' => $relation->transit_route_id,
                        'order' => $relation->order,
                        'created_at' => $relation->created_at ?? now(),
                        'updated_at' => $relation->updated_at ?? now(),
                    ]);
                }
            }
        }

        // 3. Re-add foreign key constraint and make columns non-nullable
        Schema::table('stops', function (Blueprint $table) {
            $table->foreignId('transit_route_id')->change()->constrained()->cascadeOnDelete();
            $table->integer('order')->change();
        });

        // 4. Drop the pivot table
        Schema::dropIfExists('route_stop');
    }
};
