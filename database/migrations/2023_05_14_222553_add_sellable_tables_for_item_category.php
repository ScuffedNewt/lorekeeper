<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSellableTablesForItemCategory extends Migration {
    /**
     * Run the migrations.
     */
    public function up() {
        Schema::table('item_categories', function (Blueprint $table) {
            //
            $table->boolean('can_user_sell')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down() {
        //
    }
}
