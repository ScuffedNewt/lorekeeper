<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void {
        Schema::table('character_relations', function (Blueprint $table) {
            $table->longText('parsed_info')->nullable()->default(null)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {
        Schema::table('character_relations', function (Blueprint $table) {
            $table->longText('parsed_info')->nullable()->default(null)->change();
        });
    }
};
