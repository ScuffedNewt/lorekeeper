<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDisallowTransferOptionToShopStock extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('shop_stock', function (Blueprint $table) {
            //
            $table->boolean('disallow_transfer')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('shop_stock', function (Blueprint $table) {
            //
            $table->dropColumn('disallow_transfer');
        });
    }
}
