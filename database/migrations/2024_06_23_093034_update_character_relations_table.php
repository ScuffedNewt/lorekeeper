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

        DB::statement('ALTER TABLE character_relations ADD CONSTRAINT check_chara_order CHECK (character_1_id < character_2_id)');

        DB::unprepared('
            CREATE TRIGGER before_insert_character_relations 
            BEFORE INSERT ON character_relations
            FOR EACH ROW
            BEGIN
                IF NEW.character_1_id > NEW.character_2_id THEN
                    SET @temp = NEW.character_1_id;
                    SET NEW.character_1_id = NEW.character_2_id;
                    SET NEW.character_2_id = @temp;
                END IF;
            END
        ');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {
        // This migration is not reversible
        throw new RuntimeException('This migration is not reversible.');
    }
};
