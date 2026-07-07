<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('route_revisions', function (Blueprint $table) {
            $table->json('geometry_return')->nullable()->after('geometry');
        });
    }

    public function down(): void
    {
        Schema::table('route_revisions', function (Blueprint $table) {
            $table->dropColumn('geometry_return');
        });
    }
};
