<?php namespace App\Services\Item;

use App\Services\Service;
use Illuminate\Http\Request;

use DB;

use App\Services\InventoryManager;
use App\Services\CharacterManager;

use App\Models\Item\Item;

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
            'items' => Item::orderBy('name')->pluck('name', 'id'),
            'months' => [
                '1' => 'January',
                '2' => 'February',
                '3' => 'March',
                '4' => 'April',
                '5' => 'May',
                '6' => 'June',
                '7' => 'July',
                '8' => 'August',
                '9' => 'September',
                '10' => 'October',
                '11' => 'November',
                '12' => 'December',
            ]
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
        $data['waterings'] = isset($tag->data['waterings']) ? $tag->data['waterings'] : 1;
        $data['plant_id'] = isset($tag->data['plant_id']) ? $tag->data['plant_id'] : null;
        $data['quantity'] = isset($tag->data['quantity']) ? $tag->data['quantity'] : 1;  
        $data['start_month'] = isset($tag->data['start_month']) ? $tag->data['start_month'] : null;    
        $data['end_month'] = isset($tag->data['end_month']) ? $tag->data['end_month'] : null;    

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
            $coopData['waterings'] = isset($data['waterings']) ? $data['waterings'] : 1; 
            $coopData['plant_id'] = isset($data['plant_id']) ? $data['plant_id'] : null; 
            $coopData['quantity'] = isset($data['quantity']) ? $data['quantity'] : 1;    
            $coopData['start_month'] = isset($data['start_month']) ? $data['start_month'] : null;    
            $coopData['end_month'] = isset($data['end_month']) ? $data['end_month'] : null;  

            if($coopData['plant_id'] == $tag->item->id) throw new \Exception('Cannot be the same item as the coop animal.');

            $tag->update(['data' => json_encode($coopData)]);

            return $this->commitReturn(true);
        } catch(\Exception $e) { 
            $this->setError('error', $e->getMessage());
        }
        return $this->rollbackReturn(false);
    }
}
  