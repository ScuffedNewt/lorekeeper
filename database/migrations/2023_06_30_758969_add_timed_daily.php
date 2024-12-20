<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTimedDaily extends Migration {
    /**
     * Run the migrations.
     */
    public function up() {
        Schema::table('daily', function (Blueprint $table) {
            $table->boolean('is_timed_daily')->default(false);
            $table->timestamp('start_at')->nullable()->default(null);
            $table->timestamp('end_at')->nullable()->default(null);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down() {
        Schema::table('daily', function (Blueprint $table) {
            $table->dropColumn('is_timed_daily');
            $table->dropColumn('start_at');
            $table->dropColumn('end_at');
        });
    }
}
