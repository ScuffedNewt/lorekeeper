<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePairingTables extends Migration {
    /**
     * Run the migrations.
     */
    public function up() {
        Schema::create('pairings', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id')->unsigned()->index(); // user who owns the pairing
            $table->string('data')->nullable()->default(null);

            $table->integer('character_1_id')->unsigned()->index(); // partner 1
            $table->integer('character_2_id')->unsigned()->index(); // partner 2

            $table->boolean('character_1_approved')->default(0); // approval status for character 1
            $table->boolean('character_2_approved')->default(0); // approval status for character 2
            $table->enum('status', ['PENDING', 'REJECTED', 'APPROVED', 'COMPLETE', 'CANCELLED'])->default('PENDING');

            $table->timestamps();
        });

        Schema::table('character_images', function (Blueprint $table) {
            $table->string('sex')->nullable()->default(null);
        });

        Schema::table('feature_categories', function (Blueprint $table) {
            $table->integer('max_inheritable')->default(5);
            $table->integer('min_inheritable')->default(0);
        });

        Schema::table('rarities', function (Blueprint $table) {
            $table->integer('inherit_chance')->default(50);
        });

        Schema::table('specieses', function (Blueprint $table) {
            $table->integer('inherit_chance')->default(50);
        });

        Schema::table('subtypes', function (Blueprint $table) {
            $table->integer('inherit_chance')->default(50);
        });

        Schema::table('user_items', function (Blueprint $table) {
            $table->unsignedInteger('pairing_count')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down() {
        Schema::dropIfExists('pairings');

        Schema::table('character_images', function (Blueprint $table) {
            $table->dropColumn('sex');
        });

        Schema::table('feature_categories', function (Blueprint $table) {
            $table->dropColumn('max_inheritable');
            $table->dropColumn('min_inheritable');
        });

        Schema::table('rarities', function (Blueprint $table) {
            $table->dropColumn('inherit_chance');
        });

        Schema::table('user_items', function (Blueprint $table) {
            $table->dropColumn('pairing_count');
        });

        Schema::table('specieses', function (Blueprint $table) {
            $table->dropColumn('inherit_chance');
        });

        Schema::table('subtypes', function (Blueprint $table) {
            $table->dropColumn('inherit_chance');
        });
    }
}
