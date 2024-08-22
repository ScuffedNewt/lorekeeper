<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddUpdateIpTable extends Migration {
    /**
     * Run the migrations.
     */
    public function up() {
        //
        Schema::table('user_ips', function (Blueprint $table) {
            $table->timestamp('updated_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down() {
        //
        Schema::table('user_ips', function (Blueprint $table) {
            $table->dropColumn('updated_at');
        });
    }
}
