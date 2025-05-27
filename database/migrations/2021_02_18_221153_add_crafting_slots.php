<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCraftingSlots extends Migration {
    /**
     * Run the migrations.
     */
    public function up() {
        //
        Schema::create('crafting_slots', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('currency_id')->nullable()->default(null);
            $table->unsignedInteger('slot_cost')->nullable()->default(null);
            $table->boolean('free')->default(0);

            $table->foreign('currency_id')->references('id')->on('currencies');
        });

        Schema::create('user_crafting_slots', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('user_id');
            $table->unsignedInteger('slot_id');
            $table->unsignedInteger('recipe_id')->nullable()->default(null);
            $table->timestamp('started_at')->nullable();
            $table->timestamp('end_at')->nullable();

            $table->foreign('recipe_id')->references('id')->on('recipes');
            $table->foreign('user_id')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down() {
        //
        Schema::dropIfExists('user_crafting_slots');
        Schema::dropIfExists('crafting_slots');
    }
}
