<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('city_route', function (Blueprint $table) {
            $table->id();
            $table->foreignId('city_id')->constrained()->cascadeOnDelete();
            $table->foreignId('transit_route_id')->constrained()->cascadeOnDelete();
            $table->unique(['city_id', 'transit_route_id']);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('city_route');
    }
};
