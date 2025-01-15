<?php

namespace App\Services\Stat;

use App\Models\Character\Character;
use App\Models\Character\CharacterCurrency;
use App\Models\Character\CharacterItem;
use App\Models\Level\Level;
use App\Models\User\User;
use App\Services\Service;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class LevelManager extends Service {
    /**
     * Levels up a character / user.
     *
     * @param mixed $recipient
     */
    public function level($recipient) {
        DB::beginTransaction();

        try {
            $service = new ExperienceManager;

            $level = $recipient->level;

            // getting the next level
            $next = Level::where('level', $level->current_level + 1)->where('level_type', $recipient->logType)->first();

            // validation
            if (!$next) {
                throw new \Exception('You are at the max level!');
            }
            if ($level->current_exp < $next->required_exp) {
                throw new \Exception('You do not have enough exp to level up!');
            }

            if (!$service->debitExp($recipient, 'Level Up', 'Used EXP in level up.', $level, $next->exp_required)) {
                throw new \Exception('Error debiting exp.');
            }

            foreach ($next->limits as $limit) {
                $rewardType = $limit->limit_type;
                $check = null;
                switch ($rewardType) {
                    case 'Item':
                        $check = CharacterItem::where('item_id', $limit->reward->id)->where('character_id', $character->id)->where('count', '>', 0)->first();
                        break;
                    case 'Currency':
                        $check = CharacterCurrency::where('currency_id', $limit->reward->id)->where('character_id', $character->id)->where('count', '>', 0)->first();
                        break;
                }

                if (!$check) {
                    throw new \Exception('You require '.$limit->reward->name.' to level up.');
                }
            }

            // //////////////////////////////////////////////////// LEVEL REWARDS
            $levelRewards = $this->processRewards($next);

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
                    throw new \Exception('Failed to distribute rewards to user.');
                }
            }
            // ///////////////////////////////////////////////

            // create log
            if ($this->createlog($recipient, $recipient->logType, $level->current_level, $next->level)) {
                $level->current_level += 1;
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
     *
     * @return array
     */
    private function processRewards($level) {
        $assets = createAssetsArray(false);
        // Process the additional rewards
        foreach ($level->rewards as $reward) {
            if ($reward->rewardable_type == 'Exp' || $reward->rewardable_type == 'Points') {
                addAsset($assets, $reward->rewardable_type, $reward->quantity);
            } else {
                addAsset($assets, $reward->reward, $reward->quantity);
            }
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
