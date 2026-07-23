<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up() {
        Schema::table('level_log', function (Blueprint $table) {
            $table->unsignedInteger('sender_id')->nullable()->after('id');
            $table->string('sender_type')->nullable()->after('sender_id');
            $table->string('log')->nullable()->after('new_level');
            $table->string('log_type')->nullable()->after('log');
            $table->string('data', 1024)->nullable()->after('log_type');
        });

        DB::table('level_log')->whereNull('log')->update([
            'log'      => 'Level Up',
            'log_type' => 'Level Up',
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down() {
        Schema::table('level_log', function (Blueprint $table) {
            $table->dropColumn(['sender_id', 'sender_type', 'log', 'log_type', 'data']);
        });
    }
};
