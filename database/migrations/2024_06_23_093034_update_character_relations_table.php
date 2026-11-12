<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void {
        //
        Artisan::call('update-character-relations');

        Schema::table('character_relations', function (Blueprint $table) {
            $table->renameColumn('chara_1', 'character_1_id');
            $table->renameColumn('chara_2', 'character_2_id');
            $table->timestamps();

            $table->unique(['character_1_id', 'character_2_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {
        // This migration is not reversible
        throw new RuntimeException('This migration is not reversible.');
    }
};
