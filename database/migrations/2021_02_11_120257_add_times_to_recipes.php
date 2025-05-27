<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTimesToRecipes extends Migration {
    /**
     * Run the migrations.
     */
    public function up() {
        Schema::table('recipes', function (Blueprint $table) {
            //
            $table->timestamp('open_at')->nullable()->default(null);
            $table->timestamp('close_at')->nullable()->default(null);
            $table->integer('time')->nullable()->default(null);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down() {
        Schema::table('recipes', function (Blueprint $table) {
            //
            $table->dropColumn('open_at');
            $table->dropColumn('close_at');
            $table->dropColumn('time');
        });
    }
}
