<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class MakeCharacterFolderTables extends Migration {
    /**
     * Run the migrations.
     */
    public function up() {
        Schema::create('character_folders', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->string('description')->nullable();
            $table->integer('user_id')->unsigned();
        });

        Schema::table('characters', function (Blueprint $table) {
            $table->integer('folder_id')->unsigned()->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down() {
        //
        Schema::dropIfExists('character_folders');
        Schema::table('characters', function (Blueprint $table) {
            $table->dropColumn('folder_id');
        });
    }
}
