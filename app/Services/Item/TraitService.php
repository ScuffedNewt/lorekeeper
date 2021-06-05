<?php namespace App\Services\Item;

use App\Services\Service;
use Illuminate\Http\Request;

use DB;

use App\Services\InventoryManager;
use App\Services\CharacterManager;

use App\Models\Character\Character;
use App\Models\Feature\Feature;
use App\Models\Feature\FeatureCategory;

class TraitService extends Service
{
    /*
    |--------------------------------------------------------------------------
    | Trait Service
    |--------------------------------------------------------------------------
    |
    | Handles the editing and usage of trait type items.
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
            'categories' => ['none' => 'No category'] + FeatureCategory::orderBy('sort', 'DESC')->pluck('name', 'id')->toArray(),
            'features' => Feature::getFeaturesByCategory()
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
        $tagData['features'] = isset($tag->data['features']) ? $tag->data['features'] : null;
        $tagData['quantity'] = isset($tag->data['quantity']) ? $tag->data['quantity'] : null;
        $tagData['feature_type'] = isset($tag->data['feature_type']) ? $tag->data['feature_type'] : null;
        $tagData['type_quantity'] = isset($tag->data['type_quantity']) ? $tag->data['type_quantity'] : null;

        return $tagData;
    }

    /**
     * Processes the data attribute of the tag and returns it in the preferred format for DB storage.
     *
     * @param  string  $tag
     * @param  array   $data
     * @return bool
     */
    public function updateData($tag, $data)
    {
        //put inputs into an array to transfer to the DB
        $tagData['name'] = isset($data['name']) ? $data['name'] : null;
        $tagData['species_id'] = isset($data['species_id']) && $data['species_id'] ? $data['species_id'] : null;
        $tagData['subtype_id'] = isset($data['subtype_id']) && $data['subtype_id'] ? $data['subtype_id'] : null;
        $tagData['rarity_id'] = isset($data['rarity_id']) && $data['rarity_id'] ? $data['rarity_id'] : null;
        $tagData['description'] = isset($data['description']) && $data['description'] ? $data['description'] : null;
        $tagData['parsed_description'] = parse($tagData['description']);
        $tagData['sale_value'] = isset($data['sale_value']) ? $data['sale_value'] : 0;
        //if the switch was toggled, set true, if null, set false
        $tagData['is_sellable'] = isset($data['is_sellable']);
        $tagData['is_tradeable'] = isset($data['is_tradeable']);
        $tagData['is_giftable'] = isset($data['is_giftable']);
        $tagData['is_visible'] = isset($data['is_visible']);

        DB::beginTransaction();

        try {
            //get characterData array and put it into the 'data' column of the DB for this tag
            $tag->update(['data' => json_encode($tagData)]);

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
        DB::beginTransaction();

        try {
            foreach($stacks as $key=>$stack) {
                // We don't want to let anyone who isn't the owner of the trait to use it,
                // so do some validation...
                if($stack->user_id != $user->id) throw new \Exception("This item does not belong to you.");

                // Next, try to delete the tag item. If successful, we can start distributing rewards.
                if((new InventoryManager)->debitStack($stack->user, 'Trait Used', ['data' => ''], $stack, $data['quantities'][$key])) {

                    for($q=0; $q<$data['quantities'][$key]; $q++) {
                        //fill an array with the DB contents
                        $tagData = $stack->item->tag('trait')->data;
                        //set user who is opening the item
                        $tagData['user_id'] = $user->id;
                        //other vital data that is default
                        $tagData['name'] = isset($tagData['name']) ? $tagData['name'] : "Trait";
                        $tagData['transferrable_at'] = null;
                        $tagData['is_myo_trait'] = 1;
                        //this uses your default MYO trait image from the CharacterManager
                        //see wiki page for documentation on adding a default image switch
                        $tagData['use_cropper'] = 0;
                        $tagData['x0'] = null;
                        $tagData['x1'] = null;
                        $tagData['y0'] = null;
                        $tagData['y1'] = null;
                        $tagData['image'] = null;
                        $tagData['thumbnail'] = null;
                        $tagData['artist_id'][0] = null;
                        $tagData['artist_url'][0] = null;
                        $tagData['designer_id'][0] = null;
                        $tagData['designer_url'][0] = null;
                        $tagData['feature_id'][0] = null;
                        $tagData['feature_data'][0] = null;

                        //DB has 'true' and 'false' as strings, so need to set them to true/null
                        if( $stack->item->tag('trait')->data['is_sellable'] == "true") { $tagData['is_sellable'] = true; } else $tagData['is_sellable'] = null;
                        if( $stack->item->tag('trait')->data['is_tradeable'] == "true") { $tagData['is_tradeable'] = true; } else $tagData['is_tradeable'] = null;
                        if( $stack->item->tag('trait')->data['is_giftable'] == "true") { $tagData['is_giftable'] = true; } else $tagData['is_giftable'] = null;
                        if( $stack->item->tag('trait')->data['is_visible'] == "true") { $tagData['is_visible'] = true; } else $tagData['is_visible'] = null;

                        // Distribute user rewards
                        $charService = new CharacterManager;
                        if ($tag = $charService->createCharacter($tagData, $user, true)) {
                            flash('<a href="' . $tag->url . '">MYO trait</a> created successfully.')->success();
                        }
                        else {
                            throw new \Exception("Failed to use trait.");
                        }
                    }
                }
            }
            return $this->commitReturn(true);
        } catch(\Exception $e) {
            $this->setError('error', $e->getMessage());
        }
        return $this->rollbackReturn(false);
    }
}
