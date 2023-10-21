<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up() {
        //
        Schema::table('loots', function (Blueprint $table) {
            $table->boolean('is_guaranteed')->default(false);
        });

        Schema::table('loot_tables', function (Blueprint $table) {
            $table->integer('rolls')->default(null)->nullable();
        });

        Schema::create('user_loot_drop_progress', function (Blueprint $table) {
            $table->id();
            $table->integer('user_id');
            $table->integer('loot_table_id');
            $table->integer('rolls');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down() {
        //
        Schema::table('loots', function (Blueprint $table) {
            $table->dropColumn('is_guaranteed');
        });
        Schema::table('loot_tables', function (Blueprint $table) {
            $table->dropColumn('rolls');
        });
        Schema::dropIfExists('user_loot_drop_progress');
    }
};
