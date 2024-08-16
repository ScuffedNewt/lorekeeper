<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateReferrals extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::create('referrals', function (Blueprint $table) {
            $table->id();
            $table->integer('referral_count')->unsigned()->default(1);
            $table->string('data')->nullable()->default(null);
            $table->boolean('is_active')->default(0);
            $table->integer('days_active')->unsigned()->nullable()->default(null);
            $table->boolean('on_every')->default(0);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->integer('referred_by')->unsigned()->nullable()->default(null);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::dropIfExists('referrals');
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('referred_by');
        });
    }
}
