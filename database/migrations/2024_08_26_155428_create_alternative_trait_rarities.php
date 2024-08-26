<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void {
        Schema::table('features', function (Blueprint $table) {
            $table->json('alternative_rarities')->nullable()->default(null);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {
        Schema::table('features', function (Blueprint $table) {
            $table->dropColumn('alternative_rarities');
        });
    }
};
