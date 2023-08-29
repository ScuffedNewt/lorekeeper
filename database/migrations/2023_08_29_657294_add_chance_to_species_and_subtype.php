<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddChanceToSpeciesAndSubtype extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('specieses', function (Blueprint $table) {
            //
            $table->integer('inherit_chance')->default(50);
        });

        Schema::table('subtypes', function (Blueprint $table) {
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
        Schema::table('specieses', function (Blueprint $table) {
            //
            $table->dropColumn('inherit_chance');
        });

        Schema::table('subtypes', function (Blueprint $table) {
            //
            $table->dropColumn('inherit_chance');

        });
    }
}
