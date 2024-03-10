<?php

namespace App\Console\Commands;

use App\Facades\Settings;
use Illuminate\Console\Command;
use Carbon\Carbon;
use App\Models\Weather\Season;
use App\Models\Weather\Weather;
use Illuminate\Support\Facades\DB;

class CycleWeather extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cycle-weather';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Cycles the site\'s weather.';

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
        $currentSeason = Season::where('id', Settings::get('site_season'))->first();
        if (!$currentSeason) {
            $this->info('No season found. Please set a season in the admin panel.');
            return;
        }

        $results = $currentSeason->roll();
        $finalweather = $results[0]->id;

        //change the weather
        if (!Settings::get('cycle_site_weather')) {
            // no reset setting
            $this->info('Not set to cycle weather currently. Adjust the settings if this is an error.');
        } else if(Settings::get('cycle_site_weather') == 2) {
            //weekly reset setting
            $now = Carbon::now();
            $day = $now->dayOfWeek;
            if($day != 1) {
                return;
            }
        } else if(Settings::get('cycle_site_weather') == 3) {
            // monthly reset
            $now = Carbon::now();
            $day = $now->day;
            if ($day != 1) {
                return;
            }
        }

        DB::table('site_settings')->where('key', 'site_weather')->update(['value' => $finalweather]);
        $this->info('Weather adjusted successfully.');
    }
}
