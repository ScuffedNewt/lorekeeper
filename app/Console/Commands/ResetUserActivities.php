<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class ResetUserActivities extends Command {
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:reset-user-activities';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Reset daily user activities such as garden guest waters';

    /**
     * Execute the console command.
     */
    public function handle() {
        //
        \DB::table('user_activities')->update([
            'garden_guest_waters_today' => 0,
        ]);
    }
}
