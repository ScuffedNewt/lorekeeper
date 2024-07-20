<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Element\Typing;

class UpdatingTypingsToBeStringArrays extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'updating-typings-to-be-string-arrays';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Updates the typing models where element_ids are arrays like [1,2,3] to be arrays like ["1", "2", "3"] which allows them to be searchable in the database.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        //
        $typings = Typing::all();
        $bar = $this->output->createProgressBar(count($typings));
        $bar->start();
        foreach ($typings as $typing) {
            //  cast all elements of the array element_ids to string
            $element_ids = array_map('strval', $typing->element_ids);

            $typing->element_ids = $element_ids;
            $typing->save();
            $bar->advance();
        }
        $bar->finish();
    }
}
