<?php

namespace App\Console\Commands;

use App\Models\Character\CharacterExperience;
use App\Models\Character\CharacterLevel;
use App\Models\Stat\Experience;
use App\Models\User\UserExperience;
use App\Models\User\UserLevel;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Schema;

class ConvertExperiencePoints extends Command {
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'convert-experience-points';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Converts the old experience points system to the new one.';

    /**
     * Execute the console command.
     */
    public function handle() {
        //
        if (Schema::hasColumn('level', 'exp_required')) {
            return $this->error('Experience points have already been converted.');
        }

        // Create default experience points
        // run migration to create experience points table
        $this->call('migrate');

        if (Experience::first() == null) {
            $experience = Experience::create([
                'name'        => 'Level Experience',
                'has_image'   => false,
                'description' => 'Experience points are earned by characters and users through various actions. They can be used to level up.',
                'is_visible'  => false,
            ]);
        } else {
            $experience = Experience::first();
        }

        // Convert user experience points
        // chunk the updates to avoid memory issues
        $userCount = UserLevel::count();
        $this->info("Converting experience points for {$userCount} users...");
        $bar = $this->output->createProgressBar($userCount);
        $bar->start();
        UserLevel::chunk(100, function ($userLevels) use ($bar, $experience) {
            foreach ($userLevels as $userLevel) {
                UserExperience::create([
                    'user_id'       => $userLevel->user_id,
                    'experience_id' => $experience->id,
                    'quantity'      => $userLevel->current_exp,
                ]);
                $bar->advance();
            }
        });
        $bar->finish();
        $this->info("\nDone user levels!");

        // Convert character experience points
        $characterCount = CharacterLevel::count();
        $this->info("Converting experience points for {$characterCount} characters...");
        $bar = $this->output->createProgressBar($characterCount);
        $bar->start();
        CharacterLevel::chunk(100, function ($characterLevels) use ($bar, $experience) {
            foreach ($characterLevels as $characterLevel) {
                CharacterExperience::create([
                    'character_id'  => $characterLevel->character_id,
                    'experience_id' => $experience->id,
                    'quantity'      => $characterLevel->current_exp,
                ]);
                $bar->advance();
            }
        });
        $bar->finish();
        $this->info("\nDone character levels!");

        $this->info('Experience points have been converted successfully.');
        $this->info('Dropping old experience columns from user_levels and character_levels tables...');
        Schema::table('user_levels', function ($table) {
            $table->dropColumn(['current_exp']);
        });
        Schema::table('character_levels', function ($table) {
            $table->dropColumn(['current_exp']);
        });
        $this->info('Old experience columns have been dropped successfully.');
    }
}
