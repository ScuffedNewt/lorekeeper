<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSeasonWeathers extends Migration {
    /**
     * Run the migrations.
     */
    public function up() {
        // types of weather, like sun, cloudy, anything else really
        Schema::create('weather', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->string('name', 64);
            $table->string('summary', 256)->nullable()->default(null);
            $table->text('description')->nullable()->default(null);
            $table->text('parsed_description')->nullable()->default(null);
            $table->boolean('is_visible')->default(true);
            $table->boolean('has_image')->default(0);
        });

        Schema::create('seasons', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->string('name', 64);

            $table->string('summary', 256)->nullable()->default(null);
            $table->text('description')->nullable()->default(null);
            $table->text('parsed_description')->nullable()->default(null);

            $table->timestamp('start_at')->nullable()->default(null);
            $table->timestamp('end_at')->nullable()->default(null);

            $table->boolean('is_visible')->default(true);
            $table->boolean('has_image')->default(0);
        });

        // table for outputs to roll on for the seasons
        Schema::create('season_weathers', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->integer('season_id')->unsigned();
            $table->integer('weather_id')->unsigned();
            $table->integer('weight')->unsigned();
            $table->foreign('season_id')->references('id')->on('seasons');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down() {
        //
        Schema::dropIfExists('season_weathers');
        Schema::dropIfExists('seasons');
        Schema::dropIfExists('weather');
    }
}
