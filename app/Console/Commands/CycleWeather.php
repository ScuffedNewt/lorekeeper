<?php

namespace App\Console\Commands;

use App\Facades\Settings;
use App\Models\Weather\ObjectWeather;
use App\Models\Weather\Season;
use App\Models\Weather\Weather;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CycleWeather extends Command {
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
        // weather objects
        $objectWeathers = ObjectWeather::all();
        foreach ($objectWeathers as $objectWeather) {
            $this->info('Cycling weather for '.$objectWeather->object?->name.'...');
            $this->info('Reset period: '.$objectWeather->reset_period ?? 'None');
            if (!$objectWeather->reset_period) {
                $this->info('No reset period set. Skipping...');
                continue;
            }
            switch ($objectWeather->reset_period) {
                case 'Hour':
                    // reset every hour
                    $objectWeather->changeWeather();
                    break;
                case 'Day':
                    // reset every day at 00:00
                    $now = Carbon::now();
                    $hour = $now->hour;
                    if ($hour != 0) {
                        break;
                    }
                    $objectWeather->changeWeather();
                    break;
                case 'Week':
                    // reset on Monday at 00:00
                    $now = Carbon::now();
                    $day = $now->dayOfWeek;
                    if ($day != 1) {
                        break;
                    }
                    $objectWeather->changeWeather();
                    break;
                case 'Month':
                    // reset on the first of the month at 00:00
                    $now = Carbon::now();
                    $day = $now->day;
                    if ($day != 1) {
                        break;
                    }
                    $objectWeather->changeWeather();
                    break;
                case 'Year':
                    // reset on January 1st at 00:00
                    $now = Carbon::now();
                    $day = $now->day;
                    $month = $now->month;
                    if ($day != 1 || $month != 1) {
                        break;
                    }
                    $objectWeather->changeWeather();
                    break;
                default:
                    // no reset
                    $this->info('Unknown reset period. Skipping...');
                    break;
            }
        }

        // site wide weather
        $currentSeason = Season::where('id', Settings::get('site_season'))->first();
        if (!$currentSeason) {
            // check that this is not running from the scheduler
            if (app()->runningInConsole()) {
                $this->info('No season found. Please set a season in the admin panel.');

                return;
            }
            $ask = $this->ask('No season found. Do you still want to cycle the weather? (y/n)', 'n');
            // check
            if ($ask != 'y') {
                return;
            }
        }

        if ($currentSeason) {
            $results = $currentSeason->roll();
            $finalweather = $results[0]->weather->id;
        } else {
            // select a random weather
            $randomWeather = Weather::inRandomOrder()->first();
            $finalweather = $randomWeather->id;
        }

        // change the weather
        if (!Settings::get('cycle_site_weather')) {
            // no reset setting
            $this->info('Not set to cycle weather currently. Adjust the settings if this is an error.');
        } elseif (Settings::get('cycle_site_weather') == 2) {
            // weekly reset setting
            $now = Carbon::now();
            $day = $now->dayOfWeek;
            if ($day != 1) {
                return;
            }
        } elseif (Settings::get('cycle_site_weather') == 3) {
            // monthly reset
            $now = Carbon::now();
            $day = $now->day;
            if ($day != 1) {
                return;
            }
        }

        DB::table('site_settings')->where('key', 'site_weather')->update(['value' => $finalweather]);
        $this->info('Weather adjusted successfully.');
        $this->info('New weather: '.$results[0]->weather->name.'.');
    }
}
