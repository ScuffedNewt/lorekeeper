<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddFtoShops extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        schema::table('shops', function (Blueprint $table) {
            $table->boolean('is_fto')->default(0);
        });

        Schema::table('shop_stock', function (Blueprint $table){
            $table->boolean('is_fto')->default(0);
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
        schema::table('shops', function (Blueprint $table) {
            $table->dropColumn('is_fto');
        });
        
        Schema::table('shop_stock', function (Blueprint $table){
            $table->dropColumn('is_fto');
        });
    }
}
