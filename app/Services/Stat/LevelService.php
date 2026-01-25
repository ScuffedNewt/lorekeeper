<?php

namespace App\Services\Stat;

use App\Models\Level\Level;
use App\Services\RewardService;
use App\Services\Service;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class LevelService extends Service {
    /**
     * Creates a new level.
     *
     * @param mixed $data
     * @param mixed $type
     * @param mixed $user
     */
    public function createLevel($data, $type, $user) {
        DB::beginTransaction();

        try {
            $level = Level::create([
                'level'        => $data['level'],
                'level_type'   => $type,
                'exp_required' => $data['exp_required'],
                'description'  => $data['description'],
            ]);

            $rewardService = new RewardService;
            if (!$rewardService->populateRewards(
                get_class($level),
                $level->id,
                Arr::only($data, ['rewardable_type', 'rewardable_id', 'quantity', 'rewardable_recipient']),
                false
            )) {
                foreach ($rewardService->errors()->getMessages()['error'] as $error) {
                    flash($error)->error();
                }
                throw new \Exception('Failed to create rewards.');
            }

            return $this->commitReturn($level);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }

    /**
     * Updates a level.
     *
     * @param mixed $level
     * @param mixed $data
     */
    public function updateLevel($level, $data) {
        DB::beginTransaction();

        try {
            $level->update([
                'level'        => $data['level'],
                'exp_required' => $data['exp_required'],
                'description'  => $data['description'],
            ]);

            $rewardService = new RewardService;
            if (!$rewardService->populateRewards(
                get_class($level),
                $level->id,
                Arr::only($data, ['rewardable_type', 'rewardable_id', 'quantity', 'rewardable_recipient']),
                false
            )) {
                foreach ($rewardService->errors()->getMessages()['error'] as $error) {
                    flash($error)->error();
                }
                throw new \Exception('Failed to create rewards.');
            }

            return $this->commitReturn($level);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }

    /**
     * Deletes a level.
     *
     * @param mixed $level
     * @param mixed $type
     */
    public function deleteLevel($type, $level) {
        DB::beginTransaction();

        try {
            // Check first if the level is currently owned or if some other site feature uses it
            if ($type == 'Character') {
                if (DB::table('character_levels')->where('current_level', '>=', $level->level)->exists()) {
                    throw new \Exception('At least one character has already reached this level.');
                }
            } else {
                if (DB::table('user_levels')->where('current_level', '>=', $level->level)->exists()) {
                    throw new \Exception('At least one user has already reached this level.');
                }
            }
            $level->rewards()->delete();
            $level->delete();

            return $this->commitReturn(true);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }
}
