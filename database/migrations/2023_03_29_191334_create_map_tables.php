<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMapTables extends Migration {
    /**
     * Run the migrations.
     */
    public function up() {
        Schema::create('maps', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('description')->nullable()->default(null);
            $table->boolean('is_active')->default(true);
        });

        Schema::create('map_locations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('map_id')->constrained('maps');
            $table->string('name');
            $table->string('description')->nullable()->default(null);

            $table->string('cords');
            $table->string('shape')->default('rect');

            $table->string('link')->nullable()->default(null);
            $table->string('link_type')->default('GET');
            $table->boolean('is_active')->default(true);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down() {
        Schema::dropIfExists('map_locations');
        Schema::dropIfExists('maps');
    }
}
