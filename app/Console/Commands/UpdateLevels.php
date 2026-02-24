<?php

namespace App\Console\Commands;

use App\Models\Character\CharacterLevel;
use App\Models\Level\Level;
use App\Models\User\UserLevel;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Schema;

class UpdateLevels extends Command {
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'update-levels';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Updates levels to reference by ID instead of level number.';

    /**
     * Execute the console command.
     */
    public function handle() {
        //
        if (!Schema::hasColumn('user_levels', 'current_level')) {
            return $this->error('Levels have already been converted.');
        }

        if (!Schema::hasColumn('levels', 'name')) {
            Schema::table('levels', function ($table) {
                $table->string('name');
                $table->unsignedBigInteger('previous_level_id')->nullable()->after('id');
                $table->text('parsed_description')->nullable()->after('description');
            });
        }

        // create the level 1 entry, since that is the default level for all users and characters
        if (!Level::where('level', 1)->where('level_type', 'User')->exists()) {
            Level::create([
                'exp_required'       => 0,
                'description'        => 'The starting level.',
                'parsed_description' => 'The starting level.',
                'level_type'         => 'User',
                'name'               => 'Level 1',
            ]);

            // check if there is a level 2 entry, and so on, so we can update the previous_level_id for each
            $currentLevelNumber = 1;
            while (Level::where('level', $currentLevelNumber + 1)->where('level_type', 'User')->exists()) {
                $currentLevel = Level::where('level', $currentLevelNumber)->where('level_type', 'User')->first();
                $nextLevel = Level::where('level', $currentLevelNumber + 1)->where('level_type', 'User')->first();
                $nextLevel->update(['previous_level_id' => $currentLevel->id]);
                $currentLevelNumber++;
            }
        }

        // for character levels...
        if (!Level::where('level', 1)->where('level_type', 'Character')->exists()) {
            Level::create([
                'level'              => 1,
                'exp_required'       => 0,
                'stat_points'        => 0,
                'description'        => 'The starting level.',
                'parsed_description' => 'The starting level.',
                'level_type'         => 'Character',
                'name'               => 'Level 1',
            ]);

            // check if there is a level 2 entry, and so on, so we can update the previous_level_id for each
            $currentLevelNumber = 1;
            while (Level::where('level', $currentLevelNumber + 1)->where('level_type', 'Character')->exists()) {
                $currentLevel = Level::where('level', $currentLevelNumber)->where('level_type', 'Character')->first();
                $nextLevel = Level::where('level', $currentLevelNumber + 1)->where('level_type', 'Character')->first();
                $nextLevel->update(['previous_level_id' => $currentLevel->id]);
                $currentLevelNumber++;
            }
        }

        /*
         * Data updates:
         */

        // add level_id column to user_levels
        $this->info('Adding level_id column to user_levels...');
        if (!Schema::hasColumn('user_levels', 'level_id')) {
            Schema::table('user_levels', function ($table) {
                $table->unsignedBigInteger('level_id')->nullable()->after('id');
            });
        }

        $userCount = UserLevel::count();
        $this->info("Converting levels for {$userCount} users...");
        $bar = $this->output->createProgressBar($userCount);
        $bar->start();
        UserLevel::chunk(100, function ($userLevels) use ($bar) {
            foreach ($userLevels as $userLevel) {
                if ($userLevel->current_level === null) {
                    // set to level 1
                    $level = Level::where('level', 1)->where('level_type', 'User')->first();
                    if ($level) {
                        $userLevel->update(['level_id' => $level->id]);
                    } else {
                        $this->error("Level 1 not found for user level ID {$userLevel->id}");
                    }
                    continue;
                }

                // Find the corresponding level ID
                $level = Level::where('level', $userLevel->current_level)->first();
                if ($level) {
                    $userLevel->update(['level_id' => $level->id]);
                } else {
                    $this->error("Level {$userLevel->current_level} not found for user level ID {$userLevel->id}");
                    $this->info("Please provide the level ID for level number {$userLevel->current_level}:");
                    $levelId = (int) trim(fgets(STDIN));
                    $userLevel->update(['level_id' => $levelId]);
                }
                $bar->advance();
            }
        });
        $bar->finish();
        $this->info("\nDone user levels!");

        // add level_id column to character_levels
        $this->info('Adding level_id column to character_levels...');
        if (!Schema::hasColumn('character_levels', 'level_id')) {
            Schema::table('character_levels', function ($table) {
                $table->unsignedBigInteger('level_id')->nullable()->after('id');
            });
        }

        $characterCount = CharacterLevel::count();
        $this->info("Converting levels for {$characterCount} characters...");
        $bar = $this->output->createProgressBar($characterCount);
        $bar->start();
        CharacterLevel::chunk(100, function ($characterLevels) use ($bar) {
            foreach ($characterLevels as $characterLevel) {
                if ($characterLevel->current_level === null) {
                    // set to level 1
                    $level = Level::where('level', 1)->where('level_type', 'Character')->first();
                    if ($level) {
                        $characterLevel->update(['level_id' => $level->id]);
                    } else {
                        $this->error("Level 1 not found for character level ID {$characterLevel->id}");
                    }
                    continue;
                }

                // Find the corresponding level ID
                $level = Level::where('level', $characterLevel->current_level)->first();
                if ($level) {
                    $characterLevel->update(['level_id' => $level->id]);
                } else {
                    $this->error("Level {$characterLevel->current_level} not found for character level ID {$characterLevel->id}");
                    $this->info("Please provide the level ID for level number {$characterLevel->current_level}:");
                    $levelId = (int) trim(fgets(STDIN));
                    $characterLevel->update(['level_id' => $levelId]);
                }
                $bar->advance();
            }
        });
        $bar->finish();
        $this->info("\nDone character levels!");

        // drop old current_level column from user_levels and character_levels tables
        $this->info('Dropping old current_level column from user_levels and character_levels tables...');
        Schema::table('user_levels', function ($table) {
            $table->dropColumn(['current_level']);
        });
        Schema::table('character_levels', function ($table) {
            $table->dropColumn(['current_level']);
        });

        // drop level column from levels table
        $this->info('Dropping old level column from levels table...');
        Schema::table('levels', function ($table) {
            $table->dropColumn(['level']);
        });

        $this->info('Levels have been updated successfully.');
    }
}
