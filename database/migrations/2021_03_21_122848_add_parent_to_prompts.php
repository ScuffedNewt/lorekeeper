<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddParentToPrompts extends Migration {
    /**
     * Run the migrations.
     */
    public function up() {
        Schema::table('prompts', function (Blueprint $table) {
            //
            $table->integer('parent_id')->nullable()->default(null);
            $table->integer('parent_quantity')->nullable()->default(1);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down() {
        Schema::table('prompts', function (Blueprint $table) {
            //
            $table->dropColumn('parent_id');
            $table->dropColumn('parent_quantity');
        });
    }
}
