<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void {
        Schema::table('recipes', function (Blueprint $table) {
            $table->boolean('is_choice')->default(0);
        });

        Schema::table('user_recipe_slots', function (Blueprint $table) {
            $table->string('choice_reward_data')->nullable()->default(null);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {
        Schema::table('recipes', function (Blueprint $table) {
            $table->dropColumn('is_choice');
        });

        Schema::table('user_recipe_slots', function (Blueprint $table) {
            $table->dropColumn('choice_reward_data');
        });
    }
};
