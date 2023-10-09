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
            $table->index('item_category_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down() {
        Schema::table('items', function (Blueprint $table) {
            //
            $table->dropIndex('items_item_category_id_index');
        });
    }
};
