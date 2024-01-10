<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up() {
        Schema::table('items', function (Blueprint $table) {
            //
            $table->boolean('is_recorded')->default(true);
        });

        Schema::table('item_categories', function (Blueprint $table) {
            //
            $table->boolean('is_recorded')->default(true);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down() {
        Schema::table('record_book', function (Blueprint $table) {
            //
            $table->dropColumn('is_recorded');
        });

        Schema::table('item_categories', function (Blueprint $table) {
            //
            $table->dropColumn('is_recorded');
        });
    }
};
