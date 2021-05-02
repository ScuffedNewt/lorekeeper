<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class EditPlotTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        Schema::table('user_garden_plots', function (Blueprint $table) {
            $table->integer('waterings')->nullable()->default(null);
            $table->boolean('is_dead')->default(0);
            $table->integer('time')->nullable()->default(null);
            $table->dropColumn('end_at');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
        Schema::table('user_garden_plots', function (Blueprint $table) {
            $table->dropColumn('waterings');
            $table->dropColumn('is_dead');
            $table->dropColumn('time');
            $table->timestamp('end_at')->nullable()->default(null);
        });
    }
}
