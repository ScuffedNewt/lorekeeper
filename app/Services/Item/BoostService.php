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
use App\Models\Rarity;

class BoostService extends Service
{
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
    public function getEditData()
    {

        return [
            'settings' => [
                'pairing_trait_inheritance' => 'pairing_trait_inheritance',
                'pairing_male_percentage' => 'pairing_male_percentage',
                'pairing_female_percentage' => 'pairing_female_percentage',
             ],
             'rarities' => Rarity::orderBy('sort')->pluck('name', 'id'),
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
        if(isset($data['setting']) && isset($data['rarity_id'])) throw new \Exception("You can only set either setting or rarity.");
        if(!isset($data['setting']) && !isset($data['rarity_id'])) throw new \Exception("Please choose a setting or rarity to boost.");

        if($data['setting_chance'] == 0 || $data['rarity_chance'] == 0) throw new \Exception("Percentages cannot be 0.");
        if($data['setting_chance'] > 100 || $data['rarity_chance'] > 100) throw new \Exception("Percentages cannot be greater than 100.");

        $boostData = [];

        if(isset($data['setting'])) $boostData['setting'] = $data['setting'];
        if(isset($data['setting'])) $boostData['setting_chance'] = $data['setting_chance'];

        if(isset($data['rarity_id'])) $boostData['rarity_id'] = $data['rarity_id'];
        if(isset($data['rarity_id'])) $boostData['rarity_chance'] = $data['rarity_chance'];

        DB::beginTransaction();
        
        try {
            //get pairingData array and put it into the 'data' column of the DB for this tag
            $tag->update(['data' => json_encode($boostData)]);
        
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