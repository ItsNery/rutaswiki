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
        Schema::table('cities', function (Blueprint $table) {
            $table->string('slug')->nullable()->after('name');
        });

        // Populate slugs for existing cities
        $cities = \App\Models\City::all();
        foreach ($cities as $city) {
            $slug = \Illuminate\Support\Str::slug($city->name);
            $originalSlug = $slug;
            $count = 1;
            while (\App\Models\City::where('slug', $slug)->exists()) {
                $slug = $originalSlug . '-' . $count++;
            }
            $city->slug = $slug;
            $city->save();
        }

        // Make slug non-nullable and unique
        Schema::table('cities', function (Blueprint $table) {
            $table->string('slug')->nullable(false)->unique()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cities', function (Blueprint $table) {
            $table->dropColumn('slug');
        });
    }
};
