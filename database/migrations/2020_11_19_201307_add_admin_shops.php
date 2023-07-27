<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddAdminShops extends Migration
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
            $table->boolean('is_staff')->default(0);
            $table->boolean('use_coupons')->default(0);
            $table->boolean('is_restricted')->default(0);
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
            $table->dropColumn('is_staff');
            $table->dropColumn('use_coupons');
            $table->dropColumn('is_restricted');
        });
    }
}
