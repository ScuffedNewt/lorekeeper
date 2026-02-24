<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void {
        Schema::create('experience_points', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->boolean('has_image')->default(false);
            $table->string('hash')->nullable()->default(null);
            $table->text('description')->nullable()->default(null);
            $table->text('parsed_description')->nullable()->default(null);
            $table->boolean('is_visible')->default(true);
        });

        Schema::create('user_experience_points', function (Blueprint $table) {
            $table->id();
            $table->integer('user_id')->unsigned();
            $table->foreignId('experience_id')->constrained('experience_points')->onDelete('cascade');
            $table->integer('quantity')->default(0);
            $table->json('data')->nullable()->default(null);
        });
        Schema::table('user_experience_points', function (Blueprint $table) {
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
        });

        Schema::create('character_experience_points', function (Blueprint $table) {
            $table->id();
            $table->integer('character_id')->unsigned();
            $table->foreignId('experience_id')->constrained('experience_points')->onDelete('cascade');
            $table->integer('quantity')->default(0);
            $table->json('data')->nullable()->default(null);
        });
        Schema::table('character_experience_points', function (Blueprint $table) {
            $table->foreign('character_id')->references('id')->on('characters')->cascadeOnDelete();
        });

        // rename exp_log to experience_logs
        if (Schema::hasTable('exp_log')) {
            Schema::rename('exp_log', 'experience_logs');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {
        Schema::dropIfExists('user_experience_points');
        Schema::dropIfExists('character_experience_points');
        Schema::dropIfExists('experience_points');

        // rename experience_logs back to exp_log
        if (Schema::hasTable('experience_logs')) {
            Schema::rename('experience_logs', 'exp_log');
        }
    }
};
