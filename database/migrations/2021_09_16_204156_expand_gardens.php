<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ExpandGardens extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        Schema::table('garden_plots', function (Blueprint $table) {
            $table->string('plot_type')->default('Seed');
        });

        Schema::table('user_items', function (Blueprint $table) {
            $table->integer('garden_count')->nullable()->default(0);
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
        Schema::table('garden_plots', function (Blueprint $table) {
            $table->dropColumn('plot_type');
        });

        Schema::table('user_items', function (Blueprint $table) {
            $table->dropColumn('garden_count');
        });
    }
}
