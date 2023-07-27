<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPurchaseLimitTimeframeToShopStock extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('shop_stock', function (Blueprint $table) {
            $table->text('purchase_limit_timeframe')->nullable()->default(null);
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
            $table->dropColumn('purchase_limit_timeframe');
        });
    }
}
