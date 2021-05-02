<?php namespace App\Services\Item;

use App\Services\Service;
use Illuminate\Http\Request;

use DB;

use App\Services\InventoryManager;
use App\Services\CharacterManager;

use App\Models\Item\Item;

class SeedService extends Service
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
            'items' => Item::orderBy('name')->pluck('name', 'id'),
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
        $data['waterings'] = isset($tag->data['waterings']) ? $tag->data['waterings'] : null;
        $data['plant_id'] = isset($tag->data['plant_id']) ? $tag->data['plant_id'] : null;

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
            $seedData['waterings'] = isset($data['waterings']) ? $data['waterings'] : null; 
            $seedData['plant_id'] = isset($data['plant_id']) ? $data['plant_id'] : null;    

            if($seedData['plant_id'] == $tag->item->id) throw new \Exception('Cannot be the same item as the seed.');

            $tag->update(['data' => json_encode($seedData)]);

            return $this->commitReturn(true);
        } catch(\Exception $e) { 
            $this->setError('error', $e->getMessage());
        }
        return $this->rollbackReturn(false);
    }
}
  