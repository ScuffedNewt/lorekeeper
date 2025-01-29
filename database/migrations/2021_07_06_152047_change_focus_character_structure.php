<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeFocusCharacterStructure extends Migration {
    /**
     * Run the migrations.
     */
    public function up() {
        //
        Schema::table('submission_characters', function (Blueprint $table) {
            $table->boolean('is_focus')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down() {
        //
        Schema::table('submission_characters', function (Blueprint $table) {
            $table->dropColumn('is_focus');
        });
    }
}
