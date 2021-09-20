<?php namespace App\Services\Item;

use App\Services\Service;
use Illuminate\Http\Request;

use DB;

use App\Services\InventoryManager;
use App\Services\CharacterManager;

use App\Models\Item\Item;
use App\Models\Currency\Currency;
use App\Models\Loot\LootTable;
use App\Models\Raffle\Raffle;

class CoopService extends Service
{
    /*
    |--------------------------------------------------------------------------
    | Box Service
    |--------------------------------------------------------------------------
    |
    | Handles the editing and usage of box type items.
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
            'characterCurrencies' => Currency::where('is_character_owned', 1)->orderBy('sort_character', 'DESC')->pluck('name', 'id'),
            'items' => Item::orderBy('name')->pluck('name', 'id'),
            'currencies' => Currency::where('is_user_owned', 1)->orderBy('name')->pluck('name', 'id'),
            'tables' => LootTable::orderBy('name')->pluck('name', 'id'),
            'raffles' => Raffle::where('rolled_at', null)->where('is_active', 1)->orderBy('name')->pluck('name', 'id'),
        ];
    }

    /**
     * Processes the data attribute of the tag and returns it in the preferred format for edits.
     *
     * @param  string  $tag
     * @return mixed
     */
    public function getTagData($tag)
    {
        //fetch data from DB, if there is no data then set to NULL instead
        $rewards = [];
        if(isset($tag->data['rewards'])) {
            $assets = parseAssetData($tag->data['rewards']);
            foreach($assets as $type => $a)
            {
                $class = getAssetModelString($type, false);
                foreach($a as $id => $asset)
                {
                    $rewards[] = (object)[
                        'rewardable_type' => $class,
                        'rewardable_id' => $id,
                        'quantity' => $asset['quantity']
                    ];
                }
            }
        }
        $data['feedings'] = isset($tag->data['feedings']) ? $tag->data['feedings'] : 1; 
        $data['rewards'] = $rewards;
        return $data;
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
        DB::beginTransaction();

        try {
            $coopData['feedings'] = isset($data['feedings']) ? $data['feedings'] : 1; 
            if(!isset($data['rewardable_type'])) {
                $tag->update(['data' => json_encode($coopData)]);
                return true;
            }
            else {
            
                // The data will be stored as an asset table, json_encode()d. 
                // First build the asset table, then prepare it for storage.
                $assets = createAssetsArray();
                foreach($data['rewardable_type'] as $key => $r) {
                    switch ($r)
                    {
                        case 'Item':
                            $type = 'App\Models\Item\Item';
                            break;
                        case 'Currency':
                            $type = 'App\Models\Currency\Currency';
                            break;
                        case 'LootTable':
                            $type = 'App\Models\Loot\LootTable';
                            break;
                        case 'Raffle':
                            $type = 'App\Models\Raffle\Raffle';
                            break;
                    }
                    $asset = $type::find($data['rewardable_id'][$key]);
                    addAsset($assets, $asset, $data['quantity'][$key]);
                }
                $assets = getDataReadyAssets($assets);

                $coopData['rewards'] = $assets;
            }
            $tag->update(['data' => json_encode($coopData)]);

            return $this->commitReturn(true);
        } catch(\Exception $e) { 
            $this->setError('error', $e->getMessage());
        }
        return $this->rollbackReturn(false);
    }
}
  