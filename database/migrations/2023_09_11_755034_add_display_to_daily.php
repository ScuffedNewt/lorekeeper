<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDisplayToDaily extends Migration {
    /**
     * Run the migrations.
     */
    public function up() {
        Schema::table('daily', function (Blueprint $table) {
            $table->string('progress_display')->default('none');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down() {
        Schema::table('daily', function (Blueprint $table) {
            $table->dropColumn('progress_display');
        });
    }
}
