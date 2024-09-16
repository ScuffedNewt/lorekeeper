<?php

namespace App\Console\Commands;

use App\Models\User\UserPet;
use Illuminate\Console\Command;

class AddPetLevels extends Command {
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'add-pet-levels';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Adds pet level entries to all UserPets.';

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
        $this->info('Adding pet levels...');

        $userPets = UserPet::all();
        // start bar
        $bar = $this->output->createProgressBar(count($userPets));
        $bar->start();
        foreach ($userPets as $userPet) {
            if ($userPet->level) {
                $bar->advance();
                continue;
            }
            $userPet->level()->create([
                'bonding_level' => 0,
                'bonding'       => 0,
            ]);

            $bar->advance();
        }
        $bar->finish();
        $this->info("\nPet levels added.");
    }
}
