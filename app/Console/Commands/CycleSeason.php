<?php

namespace App\Console\Commands;

use App\Facades\Settings;
use Illuminate\Console\Command;
use App\Models\Weather\Season;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class CycleSeason extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cycle-season';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Cycles the site season.';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    { 
       //change the season
       $newSeason = Season::whereNotNull('start_at')->where('start_at', '<', Carbon::now())->whereNotNull('end_at')->where('end_at', '>', Carbon::now())->first();
       
        DB::table('site_settings')->where('key', 'site_season')->update(['value' => $newSeason->id]);
        $this->info('Season adjusted successfully.');
    }
}
