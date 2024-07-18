<?php

namespace App\Services;

use App\Models\ObjectReward;
use DB;
use Illuminate\Http\Request;

class RewardManager extends Service
{
    /*
    |--------------------------------------------------------------------------
    | Admin / Reward Maker Service
    |--------------------------------------------------------------------------
    |
    | Handles creation/editing of rewards
    |
     */

    /**
     * Edit reward
     *
     * @param  \Illuminate\Http\Request    $request
     * @param  int|null                    $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function editRewards($object, $data)
    {

        DB::beginTransaction();
        try {

            // We're going to remove all rewards and reattach them with the updated data

            $object->objectRewards()->delete();

            if (isset($data['rewardable_type'])) {
                foreach ($data['rewardable_type'] as $key => $type) {
                    ObjectReward::create([
                        'object_id' => $object->id,
                        'object_type' => class_basename($object),
                        'rewardable_type' => $type,
                        'rewardable_id' => $data['rewardable_id'][$key] ?? null,
                        'quantity' => $data['reward_quantity'][$key],
                    ]);
                }
            }

            return $this->commitReturn(true);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }
        return $this->rollbackReturn(false);
    }

    /**
     * Grant rewards
     *
     * @param  array                  $data
     * @param  \App\Models\User\User  $user
     * @return mixed
     */
    public function grantRewards($object, $user, $recipient, $logtype, $logdata)
    {
        DB::beginTransaction();

        try {
            if (!$object) {
                throw new \Exception("Invalid object.");
            }

            if (!$recipient) {
                throw new \Exception("Invalid user.");
            }

            $rewards = createAssetsArray();

            foreach ($object->objectRewards as $reward) {
                addAsset($rewards, $reward->reward, $reward->quantity);
            }

            // Distribute user rewards
            if (!($rewards = fillUserAssets($rewards, null, $recipient, $logtype, $logdata))) {
                throw new \Exception('Failed to distribute rewards to user.');
            }
            flash('Rewards granted successfully.')->success();

            return $this->commitReturn(true);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }
        return $this->rollbackReturn(false);
    }

}