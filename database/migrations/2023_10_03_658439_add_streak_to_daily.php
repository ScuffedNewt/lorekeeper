<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddStreakToDaily extends Migration {
    /**
     * Run the migrations.
     */
    public function up() {
        Schema::table('daily', function (Blueprint $table) {
            $table->boolean('is_streak')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down() {
        Schema::table('daily', function (Blueprint $table) {
            $table->dropColumn('is_streak');
        });
    }
}
