<?php namespace App\Services\Item;

use App\Services\Service;
use Illuminate\Http\Request;

use DB;

class FertiliserService extends Service
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
        $data['quality'] = isset($tag->data['quality']) ? $tag->data['quality'] : null;
        $data['time_reduced'] = isset($tag->data['time_reduced']) ? $tag->data['time_reduced'] : null;
        $data['formula'] = isset($tag->data['formula']) ? $tag->data['formula'] : null;

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
            $fertData['quality'] = isset($data['quality']) ? $data['quality'] : null;
            $fertData['time_reduced'] = isset($data['time_reduced']) ? $data['time_reduced'] : null;
            $fertData['formula'] = isset($data['formula']) ? $data['formula'] : null;

            $tag->update(['data' => json_encode($fertData)]);

            return $this->commitReturn(true);
        } catch(\Exception $e) {
            $this->setError('error', $e->getMessage());
        }
        return $this->rollbackReturn(false);
    }
}
