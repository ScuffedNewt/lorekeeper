<?php

namespace App\Console\Commands;

use App\Models\Limit\Limit;
use DB;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Schema;

class ConvertRecipeLimits extends Command {
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'convert-recipe-limits';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Converts existing recipe limits to the new system.';

    /**
     * Execute the console command.
     */
    public function handle() {
        if (!Schema::hasTable('recipe_limits')) {
            $this->info('No recipe limits to convert.');

            return;
        }

        $recipeLimits = DB::table('recipe_limits')->get();
        $bar = $this->output->createProgressBar(count($recipeLimits));
        $bar->start();
        foreach ($recipeLimits as $recipeLimit) {
            Limit::create([
                'object_model' => 'App\Models\Recipe\Recipe',
                'object_id'    => $recipeLimit->recipe_id,
                'limit_type'   => 'item',
                'limit_id'     => $recipeLimit->item_id,
                'quantity'     => 1,
            ]);

            $bar->advance();
        }
        $bar->finish();

        // drop the is_restricted column from the recipes table
        Schema::table('recipes', function ($table) {
            $table->dropColumn('is_limited');
        });

        Schema::dropIfExists('recipe_limits');
    }
}