<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void {
        // rename daily to dailies
        Schema::rename('daily', 'dailies');

        Schema::table('dailies', function (Blueprint $table) {
            // Drop unneeded columns
            $table->dropColumn('is_timed_daily');
            $table->dropColumn('is_progressable');
            $table->dropColumn('is_loop');
            $table->dropColumn('is_streak');
            $table->dropColumn('has_button_image');

            // rename column
            $table->renameColumn('progress_display', 'prize_display');

            // Add new columns
            $table->json('data')->nullable()->default(null);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {
        throw new Exception('Migration cannot be reversed.');
    }
};
