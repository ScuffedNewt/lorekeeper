<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePairingTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('pairing', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->integer('user_id')->unsigned()->index(); // user who owns the pairing
            $table->integer('item_id')->unsigned()->index(); // pairing item that was used

            $table->integer('character_1_id')->unsigned()->index(); // partner 1
            $table->integer('character_2_id')->unsigned()->index(); // partner 2

            $table->boolean('character_1_approved')->default(0); // approval status for character 1
            $table->boolean('character_2_approved')->default(0); // approval status for character 2
            $table->string('status')->default('OPEN'); // OPEN=waiting for approval by at least one user, REJECTED=was closed by at least one user, READY=approved by both users & ready to become a MYO slot, USED=finished
            $table->timestamp('created_at')->useCurrent();

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('pairing');

    }
}
