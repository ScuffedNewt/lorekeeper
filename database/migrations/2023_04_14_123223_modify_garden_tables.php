<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ModifyGardenTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        Schema::table('user_garden_plots', function (Blueprint $table) {
            // $rename item_id to held_id
            $table->renameColumn('item_id', 'held_id');
            // add held_type so we can have pets in the farm / coop
            $table->string('held_type')->default('item');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
