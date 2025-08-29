<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void {
        Schema::create('runbooks', function (Blueprint $table) {
            $table->id();

            $table->string('title');
            $table->string('type'); // Design Update, ...

            $table->text('text')->nullable()->default(null);
            $table->json('data')->nullable()->default(null);

            $table->boolean('is_public')->default(true);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {
        Schema::dropIfExists('runbooks');
    }
};
