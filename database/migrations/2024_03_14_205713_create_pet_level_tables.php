<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePetLevelTables extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void {
        Schema::table('user_pets', function (Blueprint $table) {
            $table->renameColumn('chara_id', 'character_id');
            $table->integer('sort')->unsigned()->default(0);
            $table->timestamp('bonded_at')->nullable()->default(null);
        });

        Schema::create('pet_levels', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->integer('level');
            $table->integer('bonding_required')->default(0); // bonding required to level up
            $table->json('rewards')->nullable()->default(null);
        });

        // what pets are available at what level
        Schema::create('pet_level_pets', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('pet_level_id'); // Change to unsignedBigInteger
            $table->foreign('pet_level_id')->references('id')->on('pet_levels')->onDelete('cascade');
            $table->unsignedInteger('pet_id'); // Change to unsignedBigInteger
            $table->foreign('pet_id')->references('id')->on('pets')->onDelete('cascade');
            $table->json('rewards')->nullable()->default(null);

            $table->unique(['pet_level_id', 'pet_id']);
        });

        Schema::create('user_pet_levels', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('user_pet_id');
            $table->unsignedInteger('bonding_level')->nullable()->default(null);
            $table->unsignedInteger('bonding')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {
        Schema::table('user_pets', function (Blueprint $table) {
            $table->renameColumn('character_id', 'chara_id');
            $table->dropColumn('bonded_at');
            $table->dropColumn('sort');
        });
        Schema::dropIfExists('user_pet_levels');
        Schema::dropIfExists('pet_level_pets');
        Schema::dropIfExists('pet_levels');
    }
}
