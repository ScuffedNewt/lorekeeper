<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUserMailsTable extends Migration {
    /**
     * Run the migrations.
     */
    public function up() {
        Schema::create('user_mails', function (Blueprint $table) {
            $table->id();
            $table->integer('sender_id')->unsigned();
            $table->integer('recipient_id')->unsigned();
            $table->string('subject');
            $table->text('message');

            $table->boolean('seen')->default(false);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down() {
        Schema::dropIfExists('user_mails');
    }
}
