<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('transit_routes', function (Blueprint $table) {
            $table->string('slug')->nullable()->after('name');
        });

        // Populate slugs for existing routes
        $routes = \App\Models\TransitRoute::all();
        foreach ($routes as $route) {
            $slug = \Illuminate\Support\Str::slug($route->name);
            // If the slug is empty (e.g. named with special symbols only), set a fallback
            if (empty($slug)) {
                $slug = 'ruta';
            }
            $originalSlug = $slug;
            $count = 1;
            while (\App\Models\TransitRoute::where('slug', $slug)->exists()) {
                $slug = $originalSlug . '-' . $count++;
            }
            $route->slug = $slug;
            $route->save();
        }

        // Make slug non-nullable and unique
        Schema::table('transit_routes', function (Blueprint $table) {
            $table->string('slug')->nullable(false)->unique()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transit_routes', function (Blueprint $table) {
            $table->dropColumn('slug');
        });
    }
};
