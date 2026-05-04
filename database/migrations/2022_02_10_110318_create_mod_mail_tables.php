<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateModMailTables extends Migration {
    /**
     * Run the migrations.
     */
    public function up() {
        Schema::create('mod_mails', function (Blueprint $table) {
            $table->id();
            $table->integer('staff_id')->unsigned();
            $table->integer('user_id')->unsigned();
            $table->string('subject');
            $table->text('message');

            $table->boolean('issue_strike')->default(false);
            $table->integer('strike_count')->default(0);

            $table->integer('previous_strike_count')->default(0);

            $table->boolean('seen')->default(false);

            $table->timestamps();
        });

        Schema::table('user_settings', function (Blueprint $table) {
            $table->integer('strike_count')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down() {
        Schema::dropIfExists('mod_mails');
        Schema::table('user_settings', function (Blueprint $table) {
            $table->dropColumn('strike_count');
        });
    }
}
