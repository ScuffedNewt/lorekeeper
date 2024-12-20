<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDailyTimeframeAndTypeToDaily extends Migration {
    /**
     * Run the migrations.
     */
    public function up() {
        Schema::table('daily', function (Blueprint $table) {
            $table->string('daily_timeframe')->default('daily');
            $table->boolean('is_progressable')->default(0);
            $table->boolean('is_loop')->default(0);
            $table->dropColumn('is_one_off');
        });

        Schema::table('daily_rewards', function (Blueprint $table) {
            $table->integer('step')->unsigned()->default(1);
        });

        Schema::table('daily_timers', function (Blueprint $table) {
            $table->integer('step')->unsigned()->default(1);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down() {
        Schema::table('daily', function (Blueprint $table) {
            $table->dropColumn('daily_timeframe');
            $table->dropColumn('is_progressable');
            $table->dropColumn('is_loop');
            $table->boolean('is_one_off')->default(0);
        });

        Schema::table('daily_rewards', function (Blueprint $table) {
            $table->dropColumn('step');
        });

        Schema::table('daily_timers', function (Blueprint $table) {
            $table->dropColumn('step');
        });
    }
}
