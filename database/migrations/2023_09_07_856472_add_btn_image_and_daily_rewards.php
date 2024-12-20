<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddBtnImageAndDailyRewards extends Migration {
    /**
     * Run the migrations.
     */
    public function up() {
        //
        Schema::create('daily_rewards', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->integer('daily_id')->unsigned()->default(0);
            $table->string('rewardable_type');
            $table->integer('rewardable_id')->unsigned();
            $table->integer('quantity')->unsigned();
            $table->foreign('daily_id')->references('id')->on('daily');
        });

        Schema::table('daily', function (Blueprint $table) {
            $table->boolean('has_button_image')->default(0);
            $table->boolean('is_one_off')->default(0);
            $table->dropColumn('currency_id');
            $table->dropColumn('item_id');
            $table->dropColumn('max_roll');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down() {
        //
        Schema::dropIfExists('daily_rewards');

        Schema::table('daily', function (Blueprint $table) {
            $table->integer('currency_id')->nullable()->unsigned();
            $table->integer('item_id')->nullable()->unsigned();
            $table->integer('max_roll')->default(1);
            $table->dropColumn('has_button_image');
            $table->dropColumn('is_one_off');
        });
    }
}
