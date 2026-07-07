<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCoOwnerToCharacter extends Migration {
    /**
     * Run the migrations.
     */
    public function up() {
        Schema::table('characters', function (Blueprint $table) {
            //
            $table->unsignedInteger('coowner_id')->nullable()->default(null);
            $table->string('coowner_url')->nullable()->default(null);
        });

        Schema::table('character_log', function (Blueprint $table) {
            $table->string('log', 350)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down() {
        Schema::table('characters', function (Blueprint $table) {
            //
            $table->dropColumn('coowner_id');
            $table->dropColumn('coowner_url');
        });
    }
}
