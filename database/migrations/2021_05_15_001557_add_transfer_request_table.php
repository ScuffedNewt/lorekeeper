<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTransferRequestTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        Schema::create('transfer_requests', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('sender_id');
            $table->integer('recipient_id');
            $table->string('items');
            $table->text('reason')->nullable()->default(null);
            $table->enum('status', ['Pending', 'Accepted', 'Canceled', 'Rejected'])->default('Pending');
            $table->integer('staff_id')->nullable()->default(null);
            $table->text('staff_comments')->nullable()->default(null);
            $table->timestamps();
        });

        Schema::table('user_items', function (Blueprint $table) {
            $table->unsignedInteger('transfer_count')->nullable()->default(null);
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
        Schema::dropifExists('transfer_requests');
        Schema::table('user_items', function (Blueprint $table) {
            $table->dropColumn('transfer_count');
        });
    }
}
