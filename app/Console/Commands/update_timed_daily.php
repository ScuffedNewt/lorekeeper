<?php

namespace App\Console\Commands;

use App\Models\Daily\Daily;
use Carbon\Carbon;
use Illuminate\Console\Command;

class update_timed_daily extends Command {
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'update-timed-daily';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Hides timed daily when expired, or sets it active if ready.';

    /**
     * Create a new command instance.
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle() {
        //activate or deactivate dailies
        $hidedaily = Daily::where('is_timed_daily', 1)->where('is_active', 1)->where('start_at', '>', Carbon::now())
            ->orWhere('is_timed_daily', 1)->where('is_active', 1)->where('end_at', '<', Carbon::now())->get();

        $showdaily = Daily::where('is_timed_daily', 1)->where('is_active', 0)->where('start_at', '<=', Carbon::now())->where('end_at', '>=', Carbon::now())
            ->orWhere('is_timed_daily', 1)->where('is_active', 0)->where('start_at', '<=', Carbon::now())->whereNull('end_at')->get();

        //set daily that should be active to active
        foreach ($showdaily as $showdaily) {
            $showdaily->is_active = 1;
            $showdaily->save();
        }
        //hide daily that should be hidden now
        foreach ($hidedaily as $hidedaily) {
            $hidedaily->is_active = 0;
            $hidedaily->save();
        }
    }
}
