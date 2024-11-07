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
        Schema::table('object_weather', function (Blueprint $table) {
            //
            $table->boolean('use_season_weather')->default(false);
            $table->boolean('is_hidden')->default(false);
            $table->json('data')->nullable()->default(null);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('object_weather', function (Blueprint $table) {
            //
            $table->dropColumn('use_season_weather');
            $table->dropColumn('is_hidden');
            $table->dropColumn('data');
        });
    }
};
