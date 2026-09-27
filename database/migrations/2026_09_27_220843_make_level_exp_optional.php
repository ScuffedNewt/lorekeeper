<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up() {
        Schema::table('levels', function (Blueprint $table) {
            $table->unsignedInteger('exp_required')->nullable()->change();
        });
    }

    public function down() {
        // Do not turn reward-only levels into free EXP levels during rollback.
        if (DB::table('levels')->whereNull('exp_required')->exists()) {
            throw new RuntimeException('Assign EXP requirements to reward-only levels before rolling back.');
        }

        Schema::table('levels', function (Blueprint $table) {
            $table->unsignedInteger('exp_required')->nullable(false)->change();
        });
    }
};
