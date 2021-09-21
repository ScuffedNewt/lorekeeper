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
            ],
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
        $data['stages'] = isset($tag->data['stages']) ? $tag->data['stages'] : null;
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
            if(isset($data['stage_number'])) $data['stage_number'] = array_filter($data['stage_number']);
            if(isset($data['stage_image'])) $data['stage_image'] = array_filter($data['stage_image']);
            $stages = [];
            if(isset($data['stage_number']) && !empty($data['stage_number'])) {
                // if old stages exist
                if(isset($tag->data['stages']) && !empty($tag->data['stages']) && $tag->data['stages'] != '[]') {
                    foreach($tag->data['stages'] as $stage) {
                        // if the stage number is not contained in $data['stage_number'], then delete the old image
                        if(!isset($data['stage_number'][$stage])) {
                            if(file_exists(public_path('images/data/stages/'.$tag->id.'-'.$stage.'-image.png'))) {
                                $this->deleteImage(public_path('images/data/stages'), $tag->id.'-'.$stage.'-image.png');
                            }
                        }
                        else {
                            //remove from array
                            unset($data['stage_number'][$stage]);
                        }
                    }
                }
                // if not
                if(isset($data['stage_number'])) {
                    foreach($data['stage_number'] as $key => $stage)
                    {
                        if(isset($data['stage_image'][$key]) && !isset($stages[$stage])) {
                            $stages[] =  $stage;  

                            $this->handleImage($data['stage_image'][$key], public_path('images/data/stages'), $tag->id.'-'.$stage.'-image.png');
                        }
                    }
                }
            }
            else {
                if(isset($tag->data['stages']) && !empty($tag->data['stages']) && $tag->data['stages'] != '[]') {
                    foreach($tag->data['stages'] as $stage) {
                        $this->deleteImage(public_path('images/data/stages'), $tag->id.'-'.$stage.'-image.png');
                    }
                }
            }

            $seedData['waterings'] = isset($data['waterings']) ? $data['waterings'] : 1; 
            $seedData['plant_id'] = isset($data['plant_id']) ? $data['plant_id'] : null; 
            $seedData['quantity'] = isset($data['quantity']) ? $data['quantity'] : 1;    
            $seedData['start_month'] = isset($data['start_month']) ? $data['start_month'] : null;    
            $seedData['end_month'] = isset($data['end_month']) ? $data['end_month'] : null;  
            $seedData['stages'] = isset($stages) ? $stages : null;

            if($seedData['plant_id'] == $tag->item->id) throw new \Exception('Cannot be the same item as the seed.');

            $tag->update(['data' => json_encode($seedData)]);

            return $this->commitReturn(true);
        } catch(\Exception $e) { 
            $this->setError('error', $e->getMessage());
        }
        return $this->rollbackReturn(false);
    }
}
  