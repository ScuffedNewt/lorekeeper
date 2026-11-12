<?php

use App\Models\Character\CharacterRelation;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void {
        Schema::table('character_relations', function (Blueprint $table) {
            $table->boolean('character_1_accepted')->default(false);
            $table->boolean('character_2_accepted')->default(false);
        });

        $relations = CharacterRelation::get();

        foreach ($relations as $relation) {
            $relation->update([
                'character_1_accepted' => true,
                'character_2_accepted' => true,
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {
        Schema::table('character_relations', function (Blueprint $table) {
            $table->dropColumn('character_1_accepted');
            $table->dropColumn('character_2_accepted');
        });
    }
};
