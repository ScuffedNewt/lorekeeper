<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPluggableRewards extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('object_rewards', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('object_id');
            $table->string('object_type');
            $table->integer('rewardable_id');
            $table->string('rewardable_type')->default('Item');
            $table->integer('quantity')->unsigned();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('object_rewards');
    }
}