<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('transit_routes', function (Blueprint $table) {
            $table->json('geometry_return')->nullable()->after('geometry');
            $table->boolean('round_trip')->default(false)->after('geometry_return');
        });
    }

    public function down(): void
    {
        Schema::table('transit_routes', function (Blueprint $table) {
            $table->dropColumn(['geometry_return', 'round_trip']);
        });
    }
};
