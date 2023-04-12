<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up() {
        Schema::table('subtypes', function (Blueprint $table) {
            //
            $table->integer('rarity_id')->nullable()->default(null);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down() {
        Schema::table('subtypes', function (Blueprint $table) {
            //
            $table->dropColumn('rarity_id');
        });
    }
};
