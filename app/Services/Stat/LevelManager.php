<?php

namespace App\Services\Stat;

use App\Models\Character\Character;
use App\Models\Stat\Experience;
use App\Models\User\User;
use App\Services\LimitManager;
use App\Services\Service;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class LevelManager extends Service {
    /**
     * Levels up a character / user.
     *
     * @param mixed $recipient
     */
    public function levelUp($recipient) {
        DB::beginTransaction();

        try {
            $service = new ExperienceManager;

            $level = $recipient->level;
            $next = $level->nextLevel;
            // validation
            if (!$next) {
                throw new \Exception('You are at the max level!');
            }
            if ($level->current_exp < $next->required_exp) {
                throw new \Exception('You do not have enough exp to level up!');
            }

            $experience = Experience::find(config('lorekeeper.claymores_and_companions.levels.experience_id.characters'));
            if (!$experience) {
                throw new \Exception('Experience required for leveling up is not set up correctly. Please contact an administrator.');
            }

            if (count(getLimits($next))) {
                $limitService = new LimitManager;
                if (!$limitService->checkLimits($next, false, null, 'Level Up', 'Used to level up '.$recipient->displayName.' to level '.$next->level)) {
                    foreach ($limitService->errors()->getMessages()['error'] as $error) {
                        flash($error)->error();
                    }

                    throw new \Exception('Failed to level up due to limit restrictions.');
                }
            }

            if (!$service->debitExp($recipient, 'Level Up', 'Used '.$next->exp_required.' '.$experience->name.' in level up', $experience, $next->exp_required)) {
                throw new \Exception('Error debiting exp.');
            }

            $levelRewards = $this->processRewards($next, $recipient->logType == 'Character');

            // Logging data
            $levelLogType = 'Level Rewards';
            $levelData = [
                'data' => 'Received rewards for level up to level '.$next->level.'.',
            ];

            // Distribute rewards
            if ($recipient->logType == 'User') {
                if (!$levelRewards = fillUserAssets($levelRewards, null, $recipient, $levelLogType, $levelData)) {
                    throw new \Exception('Failed to distribute rewards to user.');
                }
            } else {
                if (!$levelRewards = fillCharacterAssets($levelRewards, null, $recipient, $levelLogType, $levelData)) {
                    throw new \Exception('Failed to distribute rewards to character.');
                }
            }
            // ///////////////////////////////////////////////

            // create log
            if ($this->createLog($recipient, $recipient->logType, $level->level->id, $next->id)) {
                $level->level_id = $next->id;
                $level->save();
            } else {
                throw new \Exception('Could not create log :(');
            }

            return $this->commitReturn(true);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }

    /**
     * Creates a log.
     *
     * @param mixed $user
     * @param mixed $recipientType
     * @param mixed $currentLevel
     * @param mixed $newLevel
     */
    public function createLog($user, $recipientType, $currentLevel, $newLevel) {
        return DB::table('level_log')->insert(
            [
                'recipient_id'   => $user->id,
                'leveller_type'  => $recipientType,
                'previous_level' => $currentLevel,
                'new_level'      => $newLevel,
                'created_at'     => Carbon::now(),
                'updated_at'     => Carbon::now(),
            ]
        );
    }

    /**
     * Processes reward data into a format that can be used for distribution.
     *
     * @param mixed $level
     * @param mixed $isCharacter
     *
     * @return array
     */
    private function processRewards($level, $isCharacter = false) {
        $assets = createAssetsArray($isCharacter);
        foreach ($level->rewards as $reward) {
            addAsset($assets, $reward->reward, $reward->quantity);
        }

        return $assets;
    }

    /**
     * Processes the reward data into a consumable array.
     *
     * @param mixed $levelRewards
     */
    private function processData($levelRewards) {
        $rewards = [];
        foreach ($levelRewards as $type => $a) {
            $class = getAssetModelString($type, false);
            foreach ($a as $id => $asset) {
                $rewards[] = (object) [
                    'rewardable_type' => $class,
                    'rewardable_id'   => $id,
                    'quantity'        => $asset['quantity'],
                ];
            }
        }

        return $rewards;
    }
}
