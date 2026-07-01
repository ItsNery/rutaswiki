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
        Schema::create('route_schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('transit_route_id')->constrained()->cascadeOnDelete();
            $table->string('day_type'); // weekday, saturday, sunday, holiday
            $table->string('start_time'); // e.g. "06:00"
            $table->string('end_time'); // e.g. "22:00"
            $table->integer('frequency_minutes');
            $table->timestamps();
        });

        Schema::table('transit_routes', function (Blueprint $table) {
            $table->dropColumn([
                'schedule_start_time',
                'schedule_end_time',
                'frequency_minutes',
                'weekend_frequency_minutes',
                'operating_days'
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('route_schedules');

        Schema::table('transit_routes', function (Blueprint $table) {
            $table->string('schedule_start_time')->nullable()->after('color');
            $table->string('schedule_end_time')->nullable()->after('schedule_start_time');
            $table->integer('frequency_minutes')->nullable()->after('schedule_end_time');
            $table->integer('weekend_frequency_minutes')->nullable()->after('frequency_minutes');
            $table->json('operating_days')->nullable()->after('weekend_frequency_minutes');
        });
    }
};
