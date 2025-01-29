<?php

namespace App\Services;

use App\Models\Currency\Currency;
use App\Models\Item\Item;
use App\Models\Loot\LootTable;
use App\Models\Pet\Pet;
use App\Models\Pet\PetDrop;
use App\Models\Pet\PetDropData;
use App\Models\User\UserPet;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class PetDropService extends Service {
    /*
    |--------------------------------------------------------------------------
    | Pet Drop Service
    |--------------------------------------------------------------------------
    |
    | Handles the creation, editing and distribution of pet drops.
    |
    */

    /**
     * Creates pet drop data.
     *
     * @param array $data
     *
     * @return bool|PetDropData
     */
    public function createPetDrop($data) {
        DB::beginTransaction();

        try {
            // Check to see if pet exists
            $pet = Pet::find($data['pet_id']);
            if (!$pet) {
                throw new \Exception('The selected pet is invalid.');
            }

            // Collect parameter data and encode it
            $paramData = [];
            foreach ($data['label'] as $key => $param) {
                $paramData[$param] = $data['weight'][$key];
            }

            $drop = PetDropData::create([
                'pet_id'     => $data['pet_id'],
                'parameters' => $paramData,
                'frequency'  => $data['drop_frequency'],
                'interval'   => $data['drop_interval'],
                'is_active'  => $data['is_active'] ?? 0,
                'cap'        => $data['cap'] ?? 0,
                'name'       => $data['drop_name'] ?? 'drop',
                'override'   => $data['override'] ?? 0,
            ]);

            return $this->commitReturn($drop);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }

    /**
     * Updates pet drop data.
     *
     * @param PetDropData $drop
     * @param array       $data
     *
     * @return bool|PetDropData
     */
    public function updatePetDrop($drop, $data) {
        DB::beginTransaction();

        try {
            // UPDATE 2023 - pet id can no longer change to avoid unwanted repercussion related to the pet's drop data

            // Check to see if pet exists and if drop data already exists for it.
            // $pet = Pet::find($data['pet_id']);
            // if(!$pet) throw new \Exception('The selected pet is invalid.');
            // if(PetDropData::where('pet_id', $data['pet_id'])->where('id', '!=', $drop->id)->exists()) throw new \Exception('This pet already has drop data. Consider editing the existing data instead.');

            // Collect parameter data and encode it
            $paramData = [];
            foreach ($data['label'] as $key => $param) {
                $paramData[$param] = $data['weight'][$key];
            }

            $data['rewardable_type'] ??= null;
            $data['rewardable_id'] ??= null;
            $data['min_quantity'] ??= null;
            $data['max_quantity'] ??= null;

            $drop->update([
                'parameters' => $paramData,
                'frequency'  => $data['drop_frequency'],
                'interval'   => $data['drop_interval'],
                'is_active'  => $data['is_active'] ?? 0,
                'name'       => $data['drop_name'] ?? 'drop',
                'cap'        => $data['cap'] ?? null,
                'data'       => $this->populateAssetData($data['rewardable_type'], $data['rewardable_id'], $data['min_quantity'], $data['max_quantity']),
                'override'   => $data['override'] ?? 0,
            ]);

            return $this->commitReturn($drop);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }

    /**
     * Deletes pet drop data.
     *
     * @param PetDropData $drop
     *
     * @return bool
     */
    public function deletePetDrop($drop) {
        DB::beginTransaction();

        try {
            // if (PetDrop::where('drop_id', $drop->id)->exists()) {
            //     throw new \Exception('A pet has drops using this data. Consider disabling drops instead.');
            // }

            $drop->petDrops()->delete();
            $drop->delete();

            return $this->commitReturn(true);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }

    /**********************************************************************************************

        DROP CLAIMING

    **********************************************************************************************/

    // yes this should technically be in a manager or similar
    // but it doesnt quiet fit in the pet manager so we use it here!!! yay!!

    /**
     * Claim pet drops and credit user the items from the drop.
     *
     * @param mixed $flash
     *
     * @return bool
     */
    public function claimPetDrops(UserPet $pet, $flash = true) {
        DB::beginTransaction();

        try {
            if (!$pet->drops->drops_available) {
                throw new \Exception('This pet doesn\'t have any available drops.');
            }
            if (!$pet->drops->dropData->isActive) {
                throw new \Exception('Drops are not currently active for this pet.');
            }

            $rewards = createAssetsArray();
            // these are handled like prompt rewards
            for ($i = 0; $i < $pet->drops->drops_available; $i++) {
                foreach ($pet->availableDrops as $drops) {
                    if (isset($drops->rewards(false)[strtolower($pet->drops->parameters)])) {
                        foreach ($drops->rewards(false)[strtolower($pet->drops->parameters)] as $data) {
                            // get object
                            switch ($data->rewardable_type) {
                                case 'Item':
                                    $reward = Item::find($data->rewardable_id);
                                    break;
                                case 'Currency':
                                    $reward = Currency::find($data->rewardable_id);
                                    if (!$reward->is_user_owned) {
                                        throw new \Exception('Invalid currency selected.');
                                    }
                                    break;
                                case 'LootTable':
                                    $reward = LootTable::find($data->rewardable_id);
                                    break;
                            }
                            if (!$reward) {
                                continue;
                            }
                            // get quantity
                            $quantity = mt_rand($data->min_quantity, $data->max_quantity);
                            addAsset($rewards, $reward, $quantity);
                        }
                    }
                }
            }
            if (!$final_rewards = fillUserAssets($rewards, null, $pet->user, 'Pet Drop', [
                'data'  => 'Collected from '.($pet->pet_name ? $pet->pet_name.' the '.$pet->pet->name : $pet->pet->name),
                'notes' => 'Collected '.format_date(Carbon::now()),
            ])) {
                throw new \Exception('Failed to distribute drops.');
            }

            if ($flash) {
                flash('You received: '.createRewardsString($final_rewards))->info();
            }

            // Clear the number of available drops
            $pet->drops->update(['drops_available' => 0]);

            return $this->commitReturn($rewards);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }

    /**
     * Creates pet drop.
     *
     * @param mixed $rewardable_type
     * @param mixed $rewardable_id
     * @param mixed $min_quantity
     * @param mixed $max_quantity
     */
    private function populateAssetData($rewardable_type, $rewardable_id, $min_quantity, $max_quantity) {
        $assets = [];
        if (isset($rewardable_type) && $rewardable_type) {
            foreach ($rewardable_type as $group => $types) {
                foreach ($types as $key=>$type) {
                    if (!isset($assets[$group])) {
                        $assets[$group] = createAssetsArray();
                    }
                    $reward = null;
                    switch ($type) {
                        case 'Item':
                            $reward = Item::find($rewardable_id[$group][$key]);
                            break;
                        case 'Currency':
                            $reward = Currency::find($rewardable_id[$group][$key]);
                            if (!$reward->is_user_owned) {
                                throw new \Exception('Invalid currency selected.');
                            }
                            break;
                        case 'LootTable':
                            $reward = LootTable::find($rewardable_id[$group][$key]);
                            break;
                    }
                    if (!$reward) {
                        continue;
                    }
                    addDropAsset($assets[$group], $reward, $min_quantity[$group][$key], $max_quantity[$group][$key]);
                }
            }
        }

        return ['assets' => getDataReadyDropAssets($assets)];
    }
}
