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
        Schema::dropIfExists('prompt_skills');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
        Schema::create('prompt_skills', function (Blueprint $table) {
            $table->integer('prompt_id');
            $table->integer('skill_id');
            $table->integer('quantity');
        });
    }
};
