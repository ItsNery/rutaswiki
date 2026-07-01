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
        // Resolve duplicates by renaming duplicate user names
        $users = \App\Models\User::all();
        $seen = [];
        foreach ($users as $user) {
            $name = $user->name;
            if (in_array($name, $seen)) {
                $count = 2;
                $newName = $name . ' ' . $count;
                while (\App\Models\User::where('name', $newName)->exists() || in_array($newName, $seen)) {
                    $count++;
                    $newName = $name . ' ' . $count;
                }
                $user->name = $newName;
                $user->save();
                $seen[] = $newName;
            } else {
                $seen[] = $name;
            }
        }

        Schema::table('users', function (Blueprint $table) {
            $table->string('name')->unique()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique(['name']);
        });
    }
};
