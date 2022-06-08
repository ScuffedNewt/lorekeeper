<?php namespace App\Services\Item;

use App\Services\Service;
use Illuminate\Http\Request;

use DB;

use App\Services\InventoryManager;
use App\Services\CharacterManager;

use App\Models\Character\Character;
use App\Models\Character\CharacterFeature;
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
        $tagData['feature'] = isset($tag->data['feature']) ? $tag->data['feature'] : [];
        $tagData['feature_type'] = isset($tag->data['feature_type']) ? $tag->data['feature_type'] : [];

        // get all features with the id from 'feature' or that has the category id from 'feature_type'
        $tagData['features'] = Feature::whereIn('id', $tagData['feature'])
            ->orWhere('feature_category_id', $tagData['feature_type'])
        ->orderBy('name', 'ASC')->get()->pluck('name', 'id')->toArray();

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
        // remove any null or duplicate values
        $tagData['feature'] = array_filter($data['feature']);
        $tagData['feature_type'] = array_filter($data['feature_type']);

        $tagData['feature'] = array_unique($tagData['feature']);
        $tagData['feature_type'] = array_unique($tagData['feature_type']);

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
            if(!isset($data['feature_id']) || !isset($data['character_id'])) {
                throw new \Exception('Not all parameters set.');
            }

            $character = Character::find($data['character_id']);

            foreach($stacks as $key=>$stack) {
                // We don't want to let anyone who isn't the owner use the item
                if($stack->user_id != $user->id) throw new \Exception("This item does not belong to you.");

                if((new InventoryManager)->debitStack($stack->user, 'Trait Applied', ['data' => 'Trait applied to '.$character->displayName], $stack, $data['quantities'][$key])) {
                    
                    for($q=0; $q<$data['quantities'][$key]; $q++) {
                        $old['features'] = (new CharacterManager)->generateFeatureList($character->image);
                        // add the feature to the character
                        $feature = CharacterFeature::create(['character_image_id' => $character->image->id, 'feature_id' => $data['feature_id']]);
                        // create log
                        $new['features'] = (new CharacterManager)->generateFeatureList($character->image);

                        (new CharacterManager)->createLog($user->id, null, null, null, $character->id, 'Trait Added With '.$stack->item->displayName, '#'.$character->image->id, 'character', true, $old, $new);
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
