<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateNpcCharacterTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('characters', function (Blueprint $table) {
            $table->boolean('is_npc')->default(false);
        });

        Schema::create('character_npc_information', function (Blueprint $table) {
            $table->id();
            $table->integer('character_id')->unsigned();
            $table->integer('default_affection')->unsigned()->default(0);
            $table->text('biography')->nullable()->default(null);
            $table->integer('biography_affection_requirement')->unsigned()->default(50);
        });

        Schema::create('user_npc_affection', function (Blueprint $table) {
            $table->id();
            $table->integer('character_id')->unsigned();
            $table->integer('user_id')->unsigned();
            $table->integer('affection')->unsigned()->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('characters', function (Blueprint $table) {
            $table->dropColumn('is_npc');
        });
        Schema::dropIfExists('character_npc_information');
        Schema::dropIfExists('user_npc_affection');
    }
};
