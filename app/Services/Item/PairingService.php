<?php namespace App\Services\Item;

use App\Services\Service;

use DB;

use App\Services\InventoryManager;

use App\Models\Item\Item;
use App\Models\Feature\Feature;
use App\Models\Currency\Currency;
use App\Models\Loot\LootTable;
use App\Models\Raffle\Raffle;
use App\Models\Species\Species;

class PairingService extends Service
{
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
    public function getEditData()
    {
        return [
            'features' => Feature::orderBy('name')->pluck('name', 'id'),
            'specieses' => Species::orderBy('name')->pluck('name', 'id'),

        ];
    }

    /**
     * Processes the data attribute of the tag and returns it in the preferred format.
     *
     * @param  string  $tag
     * @return mixed
     */
    public function getTagData($tag)
    {
        return $tag->data;
    }

    /**
     * Processes the data attribute of the tag and returns it in the preferred format.
     *
     * @param  string  $tag
     * @param  array   $data
     * @return bool
     */
    public function updateData($tag, $data)
    {
        //put inputs into an array to transfer to the DB
        if(!isset($data['feature_id'])) throw new \Exception("A trait must be set.");
        if(!isset($data['pairing_type'])) throw new \Exception("A pairing type must be set.");
        $specieses = isset($data['species_id']) ? array_filter($data['species_id']) : [];

        $pairingData['feature_id'] = $data['feature_id'];
        $pairingData['pairing_type'] = $data['pairing_type'];
        if(count($specieses) > 0) $pairingData['species_ids'] = $specieses;

        DB::beginTransaction();
        
        try {
            //get pairingData array and put it into the 'data' column of the DB for this tag
            $tag->update(['data' => json_encode($pairingData)]);
        
            return $this->commitReturn(true);
        } catch(\Exception $e) {
            $this->setError('error', $e->getMessage());
        }
        return $this->rollbackReturn(false);
    }


    /**
     * Acts upon the item when used from the inventory.
     *
     * @param  \App\Models\User\UserItem  $stacks
     * @param  \App\Models\User\User      $user
     * @param  array                      $data
     * @return bool
     */
    public function act($stacks, $user, $data)
    {
        // not needed
    }

}