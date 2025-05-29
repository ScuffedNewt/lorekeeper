<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void {
        //
        Schema::dropIfExists('recipe_rewards'); // this table is unused

        Schema::table('recipes', function (Blueprint $table) {
            $table->dropColumn('is_limited');
            $table->boolean('is_visible')->default(true);
            $table->integer('required_slot_id')->nullable()->default(null);
            $table->string('hash', 10)->nullable()->default(null);
        });

        Schema::table('crafting_slots', function (Blueprint $table) {
            $table->dropForeign(['currency_id']);
            $table->dropColumn('currency_id');
            $table->dropColumn('free');
            $table->dropColumn('slot_cost');
            $table->string('name');
            $table->string('description')->nullable()->default(null);
        });

        Schema::rename('crafting_slots', 'recipe_slots');
        Schema::rename('user_crafting_slots', 'user_recipe_slots');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {
        //
        Schema::rename('recipe_slots', 'crafting_slots');
        Schema::rename('user_recipe_slots', 'user_crafting_slots');

        Schema::table('recipes', function (Blueprint $table) {
            $table->boolean('is_limited')->default(false);
            $table->dropColumn('is_visible');
            $table->dropColumn('required_slot_id');
            $table->dropColumn('hash');
        });

        Schema::table('crafting_slots', function (Blueprint $table) {
            $table->unsignedBigInteger('currency_id')->nullable()->default(null);
            $table->boolean('free')->default(false);
            $table->integer('slot_cost')->default(0);
            $table->dropColumn('name');
            $table->dropColumn('description');
        });
    }
};
