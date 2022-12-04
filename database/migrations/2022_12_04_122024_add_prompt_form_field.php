<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPromptFormField extends Migration {
    /**
     * Run the migrations.
     */
    public function up() {
        //
        Schema::table('prompts', function (Blueprint $table) {
            $table->string('form_field')->nullable()->default(null);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down() {
        //
        Schema::table('prompts', function (Blueprint $table) {
            $table->dropColumn('form_field');
        });
    }
}
