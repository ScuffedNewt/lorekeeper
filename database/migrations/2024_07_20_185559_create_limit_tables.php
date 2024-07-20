<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('dynamic_limits', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable()->default(null);
            $table->text('evaluation')->nullable()->default(null);
        });

        Schema::create('limits', function (Blueprint $table) {
            $table->id();
            $table->string('object_model');
            $table->integer('object_id');

            $table->string('limit_type');
            $table->integer('limit_id');
            $table->integer('quantity')->nullable()->default(null);

            $table->boolean('debit')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dynamic_limits');
        Schema::dropIfExists('limits');
    }
};
