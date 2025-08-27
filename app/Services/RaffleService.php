<?php

namespace App\Services;

use App\Models\Raffle\Raffle;
use App\Models\Raffle\RaffleEntryReward;
use App\Models\Raffle\RaffleGroup;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class RaffleService extends Service {
    /*
    |--------------------------------------------------------------------------
    | Raffle Service
    |--------------------------------------------------------------------------
    |
    | Handles creation and modification of raffles.
    |
    */

    /**
     * Creates a raffle.
     *
     * @param array $data
     *
     * @return \App\Models\Raffle\Raffle
     */
    public function createRaffle($data) {
        DB::beginTransaction();
        if (!isset($data['is_active'])) {
            $data['is_active'] = 0;
        }
        if (!isset($data['allow_entry'])) {
            $data['allow_entry'] = 0;
        }
        if (!isset($data['is_fto'])) {
            $data['is_fto'] = 0;
        }
        if (!isset($data['unordered'])) {
            $data['unordered'] = 0;
        }
        if (!isset($data['roll_on_end']) || !isset($data['end_at'])) {
            $data['roll_on_end'] = 0;
        }
        $raffle = Raffle::create(Arr::only($data, ['name', 'is_active', 'winner_count', 'group_id', 'order', 'allow_entry', 'is_fto', 'unordered', 'ticket_cap', 'end_at', 'roll_on_end']));
        $this->populateRewards($data, $raffle);
        DB::commit();

        return $raffle;
    }

    /**
     * Updates a raffle.
     *
     * @param array                     $data
     * @param \App\Models\Raffle\Raffle $raffle
     *
     * @return \App\Models\Raffle\Raffle
     */
    public function updateRaffle($data, $raffle) {
        DB::beginTransaction();
        if (!isset($data['is_active'])) {
            $data['is_active'] = 0;
        }
        if (!isset($data['allow_entry'])) {
            $data['allow_entry'] = 0;
        }
        if (!isset($data['is_fto'])) {
            $data['is_fto'] = 0;
        }
        if (!isset($data['unordered'])) {
            $data['unordered'] = 0;
        }
        if (!isset($data['roll_on_end']) || !isset($data['end_at'])) {
            $data['roll_on_end'] = 0;
        }
        $raffle->update(Arr::only($data, ['name', 'is_active', 'winner_count', 'group_id', 'order', 'allow_entry', 'is_fto', 'unordered', 'ticket_cap', 'end_at', 'roll_on_end']));
        $this->populateRewards($data, $raffle);
        DB::commit();

        return $raffle;
    }

    /**
     * Deletes a raffle.
     *
     * @param \App\Models\Raffle\Raffle $raffle
     *
     * @return bool
     */
    public function deleteRaffle($raffle) {
        DB::beginTransaction();
        foreach ($raffle->tickets as $ticket) {
            $ticket->delete();
        }
        $raffle->delete();
        DB::commit();

        return true;
    }

    /**
     * Creates a raffle group.
     *
     * @param array $data
     *
     * @return \App\Models\Raffle\RaffleGroup
     */
    public function createRaffleGroup($data) {
        DB::beginTransaction();
        if (!isset($data['is_active'])) {
            $data['is_active'] = 0;
        }
        $group = RaffleGroup::create(Arr::only($data, ['name', 'is_active']));
        DB::commit();

        return $group;
    }

    /**
     * Updates a raffle group.
     *
     * @param array $data
     * @param mixed $group
     *
     * @return \App\Models\Raffle\Raffle
     */
    public function updateRaffleGroup($data, $group) {
        DB::beginTransaction();
        if (!isset($data['is_active'])) {
            $data['is_active'] = 0;
        }
        $group->update(Arr::only($data, ['name', 'is_active']));
        foreach ($group->raffles as $raffle) {
            $raffle->update(['is_active' => $data['is_active']]);
        }
        DB::commit();

        return $group;
    }

    /**
     * Deletes a raffle group.
     *
     * @param mixed $group
     *
     * @return bool
     */
    public function deleteRaffleGroup($group) {
        DB::beginTransaction();
        foreach ($group->raffles as $raffle) {
            $raffle->update(['group_id' => null]);
        }
        $group->delete();
        DB::commit();

        return true;
    }

    /**
     * Processes user input for creating/updating raffle rewards.
     *
     * @param array                     $data
     * @param \App\Models\Raffle\Raffle $raffle
     */
    private function populateRewards($data, $raffle) {
        // Clear the old rewards...
        $raffle->rewards()->delete();

        if (isset($data['rewardable_type'])) {
            foreach ($data['rewardable_type'] as $key => $type) {
                RaffleEntryReward::create([
                    'raffle_id'       => $raffle->id,
                    'rewardable_type' => $type,
                    'rewardable_id'   => $data['rewardable_id'][$key],
                    'quantity'        => $data['quantity'][$key],
                ]);
            }
        }
    }
}
