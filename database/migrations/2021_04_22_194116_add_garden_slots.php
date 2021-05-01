<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddGardenSlots extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        Schema::create('garden_plots', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('currency_id')->nullable()->default(null);
            $table->unsignedInteger('plot_cost')->nullable()->default(null);
            $table->boolean('free')->default(0);

            $table->foreign('currency_id')->references('id')->on('currencies');
        });

        Schema::create('user_garden_plots', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('user_id');
            $table->unsignedInteger('plot_id');
            $table->unsignedInteger('item_id')->nullable()->default(null);
            //
            $table->string('modifiers')->nullable()->default(null);
            // timestamps
            $table->timestamp('started_at')->nullable()->default(null);
            $table->timestamp('end_at')->nullable()->default(null);
            $table->timestamp('water_at')->nullable()->default(null);

            $table->foreign('item_id')->references('id')->on('items');
            $table->foreign('user_id')->references('id')->on('users');
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
        Schema::dropIfExists('user_garden_plots');
        Schema::dropIfExists('garden_plots');
    }
}
