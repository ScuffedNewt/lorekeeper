<?php

namespace App\Console\Commands;

use App\Services\BatchService;
use Illuminate\Console\Command;

class CheckBatches extends Command {
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'check-batches';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Checks if there are any batches to trigger.';

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
        //
        (new BatchService)->updateQueue();
    }
}
