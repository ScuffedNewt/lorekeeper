<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDailyWheelsTable extends Migration {
    /**
     * Run the migrations.
     */
    public function up() {
        Schema::create('daily_wheels', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->integer('daily_id')->unsigned();

            $table->string('wheel_extension', 191)->nullable()->default(null);
            $table->string('background_extension', 191)->nullable()->default(null);
            $table->string('stopper_extension', 191)->nullable()->default(null);

            $table->integer('size')->unsigned()->default(500);
            $table->string('alignment')->default('center');

            $table->integer('segment_number')->unsigned()->default(1); // segment number
            $table->text('segment_style')->nullable()->default(null); // segment data in json form used for whinwheel lib if no image is used

            $table->string('text_orientation')->nullable()->default('curved');
            $table->integer('text_fontsize')->nullable()->default(24);
        });

        Schema::table('daily', function (Blueprint $table) {
            $table->string('type')->default('Button');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down() {
        Schema::dropIfExists('daily_wheels');

        Schema::table('daily', function (Blueprint $table) {
            $table->dropColumn('type');
        });
    }
}
