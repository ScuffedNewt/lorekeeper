<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void {
        Schema::table('user_levels', function (Blueprint $table) {
            $table->renameColumn('current_points', 'stat_points');
        });

        Schema::table('character_levels', function (Blueprint $table) {
            $table->renameColumn('current_points', 'stat_points');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {
        Schema::table('user_levels', function (Blueprint $table) {
            $table->renameColumn('stat_points', 'current_points');
        });

        Schema::table('character_levels', function (Blueprint $table) {
            $table->renameColumn('stat_points', 'current_points');
        });
    }
};
