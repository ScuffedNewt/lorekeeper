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
        Schema::dropIfExists('garden_plots');
        Schema::dropIfExists('user_garden_plots');
        if (Schema::hasColumn('user_items', 'garden_count')) {
            Schema::table('user_items', function (Blueprint $table) {
                $table->dropColumn('garden_count');
            });
        }

        Schema::create('gardens', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable()->default(null);

            $table->boolean('can_perish')->default(0); // Can the garden plot contents perish?
            $table->boolean('is_active')->default(1);

            $table->boolean('has_image')->default(0);
            $table->boolean('has_background_image')->default(0);
            $table->boolean('has_plot_image')->default(0);
        });

        Schema::create('garden_plots', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('garden_id');
            $table->json('data')->nullable()->default(null);

            $table->boolean('has_image')->default(0);
        });

        Schema::create('user_garden_plots', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('plot_id');

            $table->timestamp('started_at')->nullable()->default(null);
            $table->json('data')->nullable()->default(null);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_garden_plots');
        Schema::dropIfExists('garden_plots');
        Schema::dropIfExists('gardens');
    }
};
