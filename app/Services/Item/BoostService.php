<?php

namespace App\Services\Item;

use App\Models\Item\Item;
use App\Models\Rarity;
use App\Services\Service;
use Config;
use DB;

class BoostService extends Service {
    /*
    |--------------------------------------------------------------------------
    | Boost Service
    |--------------------------------------------------------------------------
    |
    | Handles the editing and usage of Boost type items.
    |
    */

    /**
     * Retrieves any data that should be used in the item tag editing form.
     *
     * @return array
     */
    public function getEditData() {
        return [
            'settings' => str_replace('_', ' ', array_keys(config('lorekeeper.character_pairing'))),
            'rarities' => Rarity::orderBy('sort')->pluck('name', 'id'),
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
     * @param array $data
     *
     * @return bool
     */
    public function updateData($tag, $data) {
        DB::beginTransaction();

        try {
            //put inputs into an array to transfer to the DB
            if (isset($data['setting']) && isset($data['rarity_id'])) {
                throw new \Exception('You can only set either setting or rarity.');
            }
            if (!isset($data['setting']) && !isset($data['rarity_id'])) {
                throw new \Exception('Please choose a setting or rarity to boost.');
            }

            if (!isset($data['setting_chance'])) {
                $data['setting_chance'] = 0;
            }
            if (!isset($data['rarity_chance'])) {
                $data['rarity_chance'] = 0;
            }

            if ($data['setting_chance'] > 100 || $data['rarity_chance'] > 100) {
                throw new \Exception('Percentages cannot be greater than 100.');
            }

            $boostData = [];
            if (isset($data['setting'])) {
                $boostData['setting'] = $data['setting'];
                $boostData['setting_chance'] = $data['setting_chance'];
            }

            if (isset($data['rarity_id'])) {
                $boostData['rarity_id'] = $data['rarity_id'];
                $boostData['rarity_chance'] = $data['rarity_chance'];
            }

            //get pairingData array and put it into the 'data' column of the DB for this tag
            $tag->update(['data' => json_encode($boostData)]);

            return $this->commitReturn(true);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }
}
