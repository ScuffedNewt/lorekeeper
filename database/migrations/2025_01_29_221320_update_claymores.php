<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        //
        Schema::dropIfExists('level_requirements');
        Schema::table('prompts', function (Blueprint $table) {
            $table->dropColumn('level_req');
        });

        Schema::table('count_log', function (Blueprint $table) {
            $table->text('log')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
        throw new \Exception('Migration cannot be reversed.');
    }
};
