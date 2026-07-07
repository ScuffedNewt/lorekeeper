<?php

namespace App\Console\Commands;

use App\Models\Character\Character;
use DB;
use Illuminate\Console\Command;
use Settings;

class ChangeFeature extends Command {
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'change-feature';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Changes current featured character.';

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
        $slug = Character::myo()->random()->slug;
        $setting = Settings::get('featured_character');
        while ($slug == $setting) {
            $slug = Character::myo()->random()->slug;
        }

        DB::table('site_settings')->where('key', 'featured_character')->update(['value' => $slug]);
    }
}
