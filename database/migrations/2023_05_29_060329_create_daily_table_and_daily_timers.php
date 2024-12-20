<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDailyTableAndDailyTimers extends Migration {
    /**
     * Run the migrations.
     */
    public function up() {
        Schema::create('daily', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->string('name');
            $table->boolean('has_image')->default(0);
            $table->text('description')->nullable()->default(null);
            $table->text('parsed_description')->nullable()->default(null);

            $table->integer('currency_id')->nullable()->unsigned();
            $table->integer('item_id')->nullable()->unsigned();
            $table->integer('max_roll')->default(1);

            $table->integer('sort')->unsigned()->default(0);
            $table->boolean('is_active')->default(1);
        });

        Schema::create('daily_timers', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->integer('user_id')->unsigned()->index();
            $table->integer('daily_id')->unsigned()->index();
            $table->timestamp('rolled_at')->nullable()->default(null);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down() {
        Schema::dropIfExists('daily');
        Schema::dropIfExists('daily_timers');
    }
}
