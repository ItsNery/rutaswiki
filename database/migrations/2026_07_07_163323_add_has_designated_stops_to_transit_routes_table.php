<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('transit_routes', function (Blueprint $table) {
            $table->boolean('has_designated_stops')->default(false)->after('round_trip');
        });
    }

    public function down(): void
    {
        Schema::table('transit_routes', function (Blueprint $table) {
            $table->dropColumn('has_designated_stops');
        });
    }
};
