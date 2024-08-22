<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIpTable extends Migration {
    /**
     * Run the migrations.
     */
    public function up() {
        //
        Schema::create('user_ips', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id');
            $table->string('ip');
            $table->timestamp('created_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down() {
        //
        Schema::dropIfExists('user_ips');
    }
}
