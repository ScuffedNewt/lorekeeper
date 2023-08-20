<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPairingColumns extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('character_images', function (Blueprint $table) {
            //
            $table->string('sex')->nullable()->default(null);
        });

        Schema::table('feature_categories', function (Blueprint $table) {
            //
            $table->integer('max_inheritable')->default(5);
            $table->integer('min_inheritable')->default(0);

        });

        Schema::table('rarities', function (Blueprint $table) {
            //
            $table->integer('inherit_chance')->default(50);
        });

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('character_images', function (Blueprint $table) {
            //
            $table->dropColumn('sex');
        });

        Schema::table('feature_categories', function (Blueprint $table) {
            //
            $table->dropColumn('max_inheritable');
            $table->dropColumn('min_inheritable');

        });

        Schema::table('rarities', function (Blueprint $table) {
            //
            $table->dropColumn('inherit_chance');
        });
    }
}
