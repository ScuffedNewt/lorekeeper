<?php

namespace App\Services\Stat;

use App\Facades\Notifications;
use App\Models\Character\Character;
use App\Models\Level\Level;
use App\Models\Stat\Experience;
use App\Models\User\User;
use App\Services\LimitManager;
use App\Services\Service;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class LevelManager extends Service {
    /**
     * Prevent level rewards from recursively granting more levels to the same recipient.
     **/
    private static $granting = [];

    /**
     * Grant or remove level steps.
     *
     * @param mixed $data
     * @param mixed $staff
     *
     * @return bool
     */
    public function grantLevels($data, $staff) {
        DB::beginTransaction();
        try {
            Validator::make($data, [
                'names'    => 'required|array|min:1|max:10',
                'names.*'  => ['required', 'string', 'distinct', 'regex:/^(user|character)-[1-9][0-9]*$/'],
                'quantity' => 'required|integer|not_in:0',
                'data'     => 'nullable|string|max:400',
            ])->validate();

            foreach ($data['names'] as $name) {
                [$type, $id] = explode('-', $name);
                $recipient = $type == 'user' ? User::find($id) : Character::find($id);
                if (!$recipient || !$this->creditLevels($recipient, $data['quantity'], true, $staff, 'Staff Grant', $data['data'] ?? null)) {
                    throw new \Exception('Failed to adjust levels for '.$name.'.');
                }
                if (!$this->logAdminAction($staff, 'Level Grant', 'Adjusted levels by '.$data['quantity'].' for '.$name.'. '.($data['data'] ?? ''))) {
                    throw new \Exception('Failed to log level grant.');
                }
                $owner = $type == 'user' ? $recipient : $recipient->user;
                if ($owner && !Notifications::create($type == 'user' ? 'LEVEL_GRANT' : 'CHARACTER_LEVEL_GRANT', $owner, [
                    'quantity'       => $data['quantity'],
                    'sender_url'     => $staff->url,
                    'sender_name'    => e($staff->name),
                    'stat_url'       => $type == 'user' ? url('user-stats') : url('character/'.$recipient->slug.'/stats'),
                    'character_name' => $type == 'character' ? e($recipient->fullName) : '',
                    'character_url'  => $type == 'character' ? $recipient->url : '',
                ])) {
                    throw new \Exception('Failed to notify the recipient of the level grant.');
                }
            }

            return $this->commitReturn(true);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }

    /**
     * Advance through the configured progression, without spending experience.
     *
     * @param mixed $recipient
     * @param mixed $quantity
     * @param mixed $allowRemoval
     * @param mixed $sender
     * @param mixed $logType
     * @param mixed $data
     *
     * @return bool
     */
    public function creditLevels($recipient, $quantity, $allowRemoval = false, $sender = null, $logType = 'Level Reward', $data = null) {
        DB::beginTransaction();
        $key = $recipient->logType.'-'.$recipient->id;
        $ownsGuard = false;
        try {
            if (!is_int($quantity) || !$quantity || (!$allowRemoval && $quantity < 0)) {
                throw new \Exception('Level quantity must be a non-zero whole number; rewards must be positive.');
            }
            if (isset(self::$granting[$key])) {
                throw new \Exception('Level rewards cannot recursively grant levels.');
            }
            self::$granting[$key] = true;
            $ownsGuard = true;
            $recipient->newQuery()->whereKey($recipient->id)->lockForUpdate()->firstOrFail();
            $stack = $recipient->level()->lockForUpdate()->first();
            if (!$stack) {
                $root = Level::where('level_type', $recipient->logType)->whereNull('previous_level_id')->first();
                if (!$root) {
                    throw new \Exception('No level progression has been configured.');
                }
                $stack = $recipient->level()->create(['level_id' => $root->id]);
            }
            $visited = [];
            for ($i = 0; $i < abs($quantity); $i++) {
                $stack->refresh();
                $current = $stack->level;
                if (!$current || isset($visited[$current->id])) {
                    throw new \Exception('Invalid or cyclic level progression.');
                }
                $visited[$current->id] = true;
                $next = $quantity > 0 ? $current->nextLevel : $current->previousLevel;
                if (!$next || $next->level_type != $recipient->logType || isset($visited[$next->id])) {
                    throw new \Exception('The grant exceeds the configured level progression.');
                }
                $recipient->setRelation('level', $stack);
                if ($quantity > 0) {
                    if (!$this->levelUp($recipient, true, $sender, $logType, $data)) {
                        throw new \Exception('Failed to award a level and its rewards.');
                    }
                } else {
                    if (!$this->createLog($sender, $recipient, $current->id, $next->id, $logType, $data)) {
                        throw new \Exception('Failed to log level removal.');
                    }
                    $stack->update(['level_id' => $next->id]);
                }
            }

            return $this->commitReturn(true);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        } finally {
            if ($ownsGuard) {
                unset(self::$granting[$key]);
            }
            $recipient->unsetRelation('level');
        }

        return $this->rollbackReturn(false);
    }

    /**
     * Advances a recipient to a particular level.
     *
     * Recipients already at or beyond the target are left unchanged.
     *
     * @param mixed      $recipient
     * @param mixed      $target
     * @param mixed|null $sender
     * @param mixed      $logType
     * @param mixed|null $data
     */
    public function creditSpecificLevel($recipient, $target, $sender = null, $logType = 'Level Reward', $data = null) {
        DB::beginTransaction();

        try {
            if (!$target || $target->level_type != $recipient->logType) {
                throw new \Exception('The selected level does not belong to this recipient type.');
            }

            $recipient->newQuery()->whereKey($recipient->id)->lockForUpdate()->firstOrFail();
            $stack = $recipient->level()->lockForUpdate()->first();
            if (!$stack) {
                $root = Level::where('level_type', $recipient->logType)->whereNull('previous_level_id')->first();
                if (!$root) {
                    throw new \Exception('No level progression has been configured.');
                }
                $stack = $recipient->level()->create(['level_id' => $root->id]);
            }

            $current = $stack->level;
            if (!$current) {
                throw new \Exception('The recipient has an invalid current level.');
            }

            // A specific level is an unlock. Never downgrade or duplicate its rewards.
            if ($current->meetsOrExceeds($target)) {
                return $this->commitReturn(true);
            }

            $steps = 0;
            $visited = [];
            while ($current && $current->id != $target->id) {
                if (isset($visited[$current->id])) {
                    throw new \Exception('Invalid or cyclic level progression.');
                }
                $visited[$current->id] = true;
                $current = $current->nextLevel;
                $steps++;
            }

            if (!$current || !$steps) {
                throw new \Exception('The selected level is not reachable from the recipient\'s current progression.');
            }

            if (!$this->creditLevels($recipient, $steps, false, $sender, $logType, $data)) {
                throw new \Exception('Failed to grant the selected level.');
            }

            return $this->commitReturn(true);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        } finally {
            $recipient->unsetRelation('level');
        }

        return $this->rollbackReturn(false);
    }

    /**
     * Level up a recipient.
     *
     * @param mixed $recipient
     * @param mixed $isGrant
     * @param mixed $sender
     * @param mixed $logType
     * @param mixed $data
     *
     * @return bool
     */
    public function levelUp($recipient, $isGrant = false, $sender = null, $logType = 'Level Up', $data = null) {
        DB::beginTransaction();

        try {
            $service = new ExperienceManager;

            $recipient->newQuery()->whereKey($recipient->id)->lockForUpdate()->firstOrFail();
            $level = $recipient->level()->lockForUpdate()->first();
            $next = $level?->nextLevel;
            // validation
            if (!$next) {
                throw new \Exception('You are at the max level!');
            }
            if (!$isGrant && ($level->experience?->quantity ?? 0) < $next->exp_required) {
                throw new \Exception('You do not have enough exp to level up!');
            }

            $experience = Experience::find(config('lorekeeper.claymores_and_companions.levels.experience_id.'.($recipient->logType == 'User' ? 'users' : 'characters')));
            if (!$isGrant && !$experience) {
                throw new \Exception('Experience required for leveling up is not set up correctly. Please contact an administrator.');
            }

            if (!$isGrant && count(getLimits($next))) {
                $limitService = new LimitManager;
                if (!$limitService->checkLimits($next, false, null, 'Level Up', 'Used to level up '.$recipient->displayName.' to level '.$next->name)) {
                    foreach ($limitService->errors()->getMessages()['error'] as $error) {
                        flash($error)->error();
                    }

                    throw new \Exception('Failed to level up due to limit restrictions.');
                }
            }

            if (!$isGrant && !$service->debitExp($recipient, 'Level Up', 'Used '.$next->exp_required.' '.$experience->name.' in level up', $experience, $next->exp_required)) {
                throw new \Exception('Error debiting exp.');
            }

            // create log
            if ($this->createLog($sender ?: $recipient, $recipient, $level->level->id, $next->id, $logType, $data)) {
                $level->level_id = $next->id;
                $level->save();
            } else {
                throw new \Exception('Could not create log :(');
            }

            $levelRewards = $this->processRewards($next, $recipient->logType == 'Character');

            // Logging data
            $levelLogType = 'Level Rewards';
            $levelData = [
                'data' => 'Received rewards for level up to level '.$next->name.'.',
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

            return $this->commitReturn(true);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }

    /**
     * Creates a log.
     *
     * @param mixed      $currentLevel
     * @param mixed      $newLevel
     * @param mixed      $sender
     * @param mixed      $recipient
     * @param mixed      $logType
     * @param mixed|null $data
     */
    public function createLog($sender, $recipient, $currentLevel, $newLevel, $logType = 'Level Up', $data = null) {
        return DB::table('level_log')->insert(
            [
                'sender_id'      => $sender?->id,
                'sender_type'    => $sender?->logType,
                'recipient_id'   => $recipient->id,
                'leveller_type'  => $recipient->logType,
                'previous_level' => $currentLevel,
                'new_level'      => $newLevel,
                'log'            => $logType.($data ? ' ('.$data.')' : ''),
                'log_type'       => $logType,
                'data'           => $data,
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
