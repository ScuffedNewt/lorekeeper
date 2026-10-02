<?php

namespace App\Services;

use App\Models\Pet\Pet;
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
            if (!isset($data['label']) || !isset($data['weight']) || count($data['label']) != count($data['weight'])) {
                throw new \Exception('Invalid parameters provided.');
            }

            // Collect parameter data and encode it
            $paramData = [];
            foreach ($data['label'] as $key => $param) {
                if (preg_match('/\s/', $param)) {
                    throw new \Exception('Group labels can not have spaces.');
                }
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

            // update existing pets to have the new drop data
            $existingPets = UserPet::where('pet_id', $data['pet_id'])->get();
            foreach ($existingPets as $pet) {
                $pet->drops->update([
                    'drop_id'         => $drop->id,
                    'parameters'      => $drop->rollParameters(),
                    'drops_available' => 0,
                    'next_day'        => Carbon::now()
                        ->add($drop->frequency, $drop->interval)
                        ->startOf($drop->interval),
                ]);
            }

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
            // Collect parameter data and encode it
            $paramData = [];
            if (isset($data['label'])) {
                foreach ($data['label'] as $key => $param) {
                    if (preg_match('/\s/', $param)) {
                        throw new \Exception('Group labels can not have spaces.');
                    }
                    $paramData[$param] = $data['weight'][$key];
                }
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
            $drop->refresh();

            // update existing pets to have the new drop data
            // the changes are limited to avoid removing things from players
            $existingPets = UserPet::where('pet_id', $drop->pet_id)->get();
            foreach ($existingPets as $pet) {
                $petDrop = $pet->drops;
                // update the parameters if parameter no longer exists
                if (!in_array($petDrop->parameters, $data['label'])) {
                    $petDrop->parameters = $drop->rollParameters();
                }
                $petDrop->save();
            }

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
            // instead of deleting pet drops now, we set all of the relevant ones to null
            $drops = $drop->petDrops;
            foreach ($drops as $drop) {
                $drop->update([
                    'drop_id'         => null,
                    'parameters'      => null,
                    'drops_available' => 0,
                    'next_day'        => null,
                ]);
            }
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
                throw new \Exception($pet->displayName.' pet doesn\'t have any available drops.');
            }
            if (!$pet->drops->dropData->isActive) {
                throw new \Exception('Drops are not currently active for this pet.');
            }

            $rewards = createAssetsArray();
            // these are handled like prompt rewards
            for ($i = 0; $i < $pet->drops->drops_available; $i++) {
                $drops = $pet->availableDrops;
                if (isset($drops->rewards(false)[strtolower($pet->drops->parameters)])) {
                    foreach ($drops->rewards(false)[strtolower($pet->drops->parameters)] as $data) {
                        // get object
                        $reward = getAssetModelString(strtolower($data->rewardable_type))::find($data->rewardable_id);
                        if (!$reward) {
                            continue;
                        }
                        // get quantity
                        $quantity = mt_rand($data->min_quantity, $data->max_quantity);
                        addAsset($rewards, $reward, $quantity);
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
                flash($pet->displayName.' dropped: '.createRewardsString($final_rewards))->info();
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
                    $reward = getAssetModelString(strtolower($type))::find($rewardable_id[$group][$key]);
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
