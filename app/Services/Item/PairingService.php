<?php

namespace App\Services\Item;

use App\Models\Feature\Feature;
use App\Models\Item\Item;
use App\Models\Species\Species;
use App\Models\Species\Subtype;
use App\Services\Service;
use DB;

class PairingService extends Service {
    /*
    |--------------------------------------------------------------------------
    | Pairing Service
    |--------------------------------------------------------------------------
    |
    | Handles the editing and usage of Pairing type items.
    |
    */

    /**
     * Retrieves any data that should be used in the item tag editing form.
     *
     * @return array
     */
    public function getEditData() {
        return [
            'features'  => Feature::orderBy('name')->pluck('name', 'id'),
            'specieses' => Species::orderBy('name')->pluck('name', 'id'),
            'subtypes'  => Subtype::orderBy('name')->pluck('name', 'id'),
        ];
    }

    /**
     * Processes the data attribute of the tag and returns it in the preferred format.
     *
     * @param mixed $tag
     *
     * @return mixed
     */
    public function getTagData($tag) {
        return $tag->data;
    }

    /**
     * Processes the data attribute of the tag and returns it in the preferred format.
     *
     * @param mixed $tag
     * @param array  $data
     *
     * @return bool
     */
    public function updateData($tag, $data) {
        //put inputs into an array to transfer to the DB
        if (isset($data['feature_id']) && isset($data['species_id'])) {
            throw new \Exception('You can only set either trait or species.');
        }
        if ($data['min'] == 0 || $data['max'] == 0) {
            throw new \Exception('Min/Max cannot be 0.');
        }
        if ($data['min'] > $data['max']) {
            throw new \Exception('Min must be smaller than max.');
        }

        $pairingData = [];

        $specieses = isset($data['illegal_species_ids']) ? array_filter($data['illegal_species_ids']) : [];
        $features = isset($data['illegal_feature_ids']) ? array_filter($data['illegal_feature_ids']) : [];
        $subtypes = isset($data['illegal_subtype_ids']) ? array_filter($data['illegal_subtype_ids']) : [];

        if (isset($data['feature_id'])) {
            $pairingData['feature_id'] = $data['feature_id'];
        }
        if (isset($data['species_id'])) {
            $pairingData['species_id'] = $data['species_id'];
        }
        if (isset($data['subtype_id'])) {
            $pairingData['subtype_id'] = $data['subtype_id'];
        }
        if (isset($data['default_species_id'])) {
            $pairingData['default_species_id'] = $data['default_species_id'];
        }
        if (isset($data['default_subtype_id'])) {
            $pairingData['default_subtype_id'] = $data['default_subtype_id'];
        }
        if (isset($data['pairing_type'])) {
            $pairingData['pairing_type'] = $data['pairing_type'];
        }
        $pairingData['min'] = $data['min'];
        $pairingData['max'] = $data['max'];

        if (count($specieses) > 0) {
            $pairingData['illegal_species_ids'] = $specieses;
        }
        if (count($features) > 0) {
            $pairingData['illegal_feature_ids'] = $features;
        }
        if (count($subtypes) > 0) {
            $pairingData['illegal_subtype_ids'] = $subtypes;
        }

        DB::beginTransaction();

        try {
            //get pairingData array and put it into the 'data' column of the DB for this tag
            $tag->update(['data' => json_encode($pairingData)]);

            return $this->commitReturn(true);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }

    /**
     * Acts upon the item when used from the inventory.
     *
     * @param \App\Models\User\UserItem $stacks
     * @param \App\Models\User\User     $user
     * @param array                     $data
     *
     * @return bool
     */
    public function act($stacks, $user, $data) {
        // not needed
    }
}
