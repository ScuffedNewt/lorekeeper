<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateGardenPlots extends Migration {
    /**
     * Run the migrations.
     */
    public function up() {
        // change garden_plot plot_type default
        Schema::table('garden_plots', function (Blueprint $table) {
            $table->string('plot_type')->default('seed')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down() {
        // change garden_plot plot_type default
        Schema::table('garden_plots', function (Blueprint $table) {
            $table->string('plot_type')->default('Seed')->change();
        });
    }
}
