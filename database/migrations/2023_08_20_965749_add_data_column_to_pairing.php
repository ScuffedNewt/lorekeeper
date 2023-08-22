<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDataColumnToPairing extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('pairing', function (Blueprint $table) {
            //
            $table->string('data')->nullable()->default(null);
            $table->dropColumn('item_id');

        });

        Schema::table('user_items', function (Blueprint $table) {
            //
            $table->unsignedInteger('pairing_count')->default(0);
        });


    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('pairing', function (Blueprint $table) {
            //
            $table->dropColumn('data');
            $table->integer('item_id')->unsigned()->index();

        });

        Schema::table('user_items', function (Blueprint $table) {
            //
            $table->dropColumn('pairing_count');
        });

    }
}
