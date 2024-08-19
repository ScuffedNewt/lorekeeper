<?php

namespace App\Console\Commands;

use App\Facades\Settings;
use App\Models\Weather\Season;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CycleSeason extends Command {
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
        $newSeason = Season::where('start_month', '<=', Carbon::now()->month)->orderBy('start_month', 'desc')->first();

        if ($newSeason->id == Settings::get('site_season')) {
            $this->info('Season already set to '.$newSeason->name.'.');

            return;
        }

        if (!$newSeason | $newSeason->end_month < Carbon::now()->month) {
            $this->info('No season found.');
            Settings::set('site_season', null);

            return;
        }

        DB::table('site_settings')->where('key', 'site_season')->update(['value' => $newSeason->id]);
        $this->info('Season adjusted successfully.');
    }
}
