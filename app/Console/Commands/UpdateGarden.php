<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class UpdateGarden extends Command {
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'update-garden';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Prepares the garden for latest updates.';

    /**
     * Create a new command instance.
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle() {
        // get all existing plots and update the type to be lowercase
        $plots = \App\Models\GardenPlot::all()->each(function ($plot) {
            $plot->plot_type = strtolower($plot->plot_type);
            $plot->save();
        });
        // run migrate
        $this->call('migrate');
        // run optimize twice
        $this->call('optimize');
        $this->call('optimize');
        // i dont remember if i need to do anything else lord save me
    }
}
