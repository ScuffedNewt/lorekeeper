<?php

use App\Console\Commands\FixPetDropIds;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schema;
use Symfony\Component\Console\Output\ConsoleOutput;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void {
        Schema::table('pet_drops', function (Blueprint $table) {
            // Specific drop data being used, as well as associated character
            $table->integer('drop_id')->unsigned()->nullable()->change();
        });

        $output = new ConsoleOutput;
        $output->writeln("\n<info>Calling fix-pet-drop-ids command to fix pet data inconsistency. Please do not kill the console until it is completed.</info>");
        Artisan::call(FixPetDropIds::class, [], $output);
        $output->writeln("\n<info>Command finished. You may run it again with php artisan fix-pet-drop-ids if needed.</info>");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {
        Schema::table('pet_drops', function (Blueprint $table) {
            // Specific drop data being used, as well as associated character
            $table->integer('drop_id')->unsigned()->change();
        });

        // i am not reversing that console command lol, it can be run multiple times without issue
    }
};
