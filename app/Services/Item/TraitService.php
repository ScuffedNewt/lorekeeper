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
        $tagData['trait'] = isset($tag->data['trait']) ? $tag->data['trait'] : null;
        $tagData['require_trait'] = isset($tag->data['require_trait']) ? $tag->data['require_trait'] : null;
        $tagData['replace_trait'] = isset($tag->data['replace_trait']) ? $tag->data['replace_trait'] : null;
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

        $tagData['trait'] = isset($data['trait']) ? $data['trait'] : null;
        $tagData['require_trait'] = isset($data['require_trait']) ? $data['require_trait'] : null;
        $tagData['replace_trait'] = isset($data['replace_trait']) ? $data['replace_trait'] : null;

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
            $tag = $stacks->first()->item->tag($data['tag']);

            foreach($stacks as $key=>$stack) {
                // We don't want to let anyone who isn't the owner use the item
                if($stack->user_id != $user->id) throw new \Exception("This item does not belong to you.");

                if((new InventoryManager)->debitStack($stack->user, 'Trait Applied', ['data' => 'Trait applied to '.$character->displayName], $stack, $data['quantities'][$key])) {
                    
                    for($q=0; $q<$data['quantities'][$key]; $q++) {
                        $old['features'] = (new CharacterManager)->generateFeatureList($character->image);
                        // check if the item requires a trait
                        if(isset($tag->data['require_trait'])) {
                            // check if the character has the trait
                            if(!$character->image->features()->where('feature_id', $tag->data['trait'])->exists()) {
                                throw new \Exception("This item requires a trait that the character does not have.");
                            }
                            // check if we replace the trait
                            if(isset($tag->data['replace_trait'])) {
                                // delete the trait
                                $trait = CharacterFeature::where('feature_id', $tag->data['trait'])->where('character_image_id', $character->image->id)->first();
                                $trait->delete();
                            }
                        }
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
