<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFeeToDaily extends Migration {
    /**
     * Run the migrations.
     */
    public function up() {
        Schema::table('daily', function (Blueprint $table) {
            $table->integer('fee')->unsigned()->default(0);
            $table->integer('currency_id')->nullable()->unsigned();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down() {
        Schema::table('daily', function (Blueprint $table) {
            $table->dropColumn('fee');
            $table->dropColumn('currency_id');
        });
    }
}
