<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void {
        Schema::create('object_weather', function (Blueprint $table) {
            $table->id();
            $table->string('object_model');
            $table->unsignedBigInteger('object_id');
            $table->json('weathers');
            $table->json('active_weathers')->nullable()->default(null);
            $table->string('reset_period')->nullable()->default(null);
        });

        Schema::table('seasons', function (Blueprint $table) {
            $table->dropColumn('start_at');
            $table->dropColumn('end_at');

            $table->integer('start_month')->nullable()->default(null);
            $table->integer('end_month')->nullable()->default(null);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {
        Schema::dropIfExists('object_weather');

        Schema::table('seasons', function (Blueprint $table) {
            $table->timestamp('start_at')->nullable()->default(null);
            $table->timestamp('end_at')->nullable()->default(null);

            $table->dropColumn('start_month');
            $table->dropColumn('end_month');
        });
    }
};
