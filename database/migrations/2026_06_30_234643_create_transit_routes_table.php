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
        Schema::create('transit_routes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('city_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('route_number')->nullable();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('transport_type');
            $table->json('geometry'); // GeoJSON LineString
            $table->string('color')->default('#3b82f6');
            $table->string('status')->default('published');
            $table->integer('vote_score')->default(0);
            $table->integer('revision_count')->default(1);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transit_routes');
    }
};
