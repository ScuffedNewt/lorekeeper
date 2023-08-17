<?php namespace App\Services;

use App\Services\Service;

use Carbon\Carbon;

use DB;
use Config;
use Image;
use Notifications;
use Settings;
use File;


use Illuminate\Support\Arr;
use App\Models\User\User;
use App\Models\Pairing\Pairing;
use App\Models\Species\Species;
use App\Models\Species\Subtype;
use App\Models\Rarity;
use App\Models\Feature\Feature;
use App\Models\Character\CharacterTransformation as Transformation;
use App\Models\Character\Character;
use App\Models\Character\CharacterFeature;
use App\Models\Item\Item;
use App\Models\Item\ItemTag;
use App\Models\User\UserItem;

class PairingManager extends Service
{
    /*
    |--------------------------------------------------------------------------
    | Pairing Manager
    |--------------------------------------------------------------------------
    |
    | Handles creation and modification of pairing data.
    |
    */


    /**
     * Creates a new pairing.
     *
     * @return \App\Models\Pairing\Pairing|bool
     */
    public function createPairing($character_1_code, $character_2_code, $item_id, $user)
    {
        DB::beginTransaction();

        try {
            
            if(!isset($item_id))  throw new \Exception("Pairing item not set.");
            //check that user owns this item
            if(!$user->items()->where('item_id', $item_id)->exists()) throw new \Exception("You do not own the needed pairing item.");
            // check 2 character codes are set and they are not the same character
            if(!isset($character_1_code) || !isset($character_2_code)) throw new \Exception("Please enter two character codes.");
            if($character_1_code == $character_2_code) throw new \Exception("Pairings must be between two different characters.");

            $character_1 = Character::where('slug', $character_1_code)->first();
            $character_2 = Character::where('slug', $character_2_code)->first();
            $species_1_id = $character_1->image->species->id;
            $species_2_id = $character_2->image->species->id;
            $item = Item::where('id', $item_id)->first();
            $tag = $item->tag('pairing');

            if(!isset($tag)) throw new \Exception("Item is missing the required pairing tag.");

            //check sex if set to do so. If one char has no sex it always works.
            if(Settings::get('pairing_sex_restrictions') == 1){
                if(isset($character_1->image->sex) && isset($character_2->image->sex)){
                    if($character_1->image->sex == $character_2->image->sex)  throw new \Exception("Pairings can only be created between a male and female character.");
                }
            }

            //check cooldown if set to do so. 
            $cooldownDays = Settings::get('pairing_cooldown');
            if( $cooldownDays != 0){
                $pairingsCharacter1 = Pairing::where(function($query) use ($character_1){
                    $query->where('character_1_id', $character_1->id)
                    ->orWhere('character_2_id', $character_1->id);
                })->where( 'created_at', '>', Carbon::now()->subDays($cooldownDays))->get();
                if(!$pairingsCharacter1->isEmpty()) throw new \Exception("Character 1 cannot be paired right now due to the pairing cooldown of ".$cooldownDays."days!");
                
                $pairingsCharacter2 = Pairing::where(function($query)use ($character_2){
                    $query->where('character_1_id', $character_2->id)
                    ->orWhere('character_2_id', $character_2->id);
                })->where( 'created_at', '>', Carbon::now()->subDays($cooldownDays))->get();
                if(!$pairingsCharacter2->isEmpty()) throw new \Exception("Character 2 cannot be paired right now due to the pairing cooldown of ".$cooldownDays."days!");
            }

            $specieses = (isset($tag->getData()["species_ids"])) ? $tag->getData()["species_ids"] : null;
            $pairing_type = $tag->getData()["pairing_type"];

            //check if the pairing type matches the character input
            //pairing type 0 = species, 1 = subtype
            if($pairing_type == 1 && $species_1_id != $species_2_id ) throw new \Exception("A subtype pairing can only be done with characters of the same species.");
            if($pairing_type == 0 && $species_1_id == $species_2_id ) throw new \Exception("A species pairing can only be done with characters of different species.");

            // check if correct item was used for the characters
            $valid_species_ids = [];
            if($specieses == null) {
                $valid_species_ids = [$species_1_id, $species_2_id];
            } else {
                if(in_array($species_1_id, $specieses)) $valid_species_ids[] = $species_1_id;
                if(in_array($species_2_id, $specieses)) $valid_species_ids[] = $species_2_id;
            }

            // if no species is set all are valid
            // otherwise we need at least 1 valid species that can receive the traits...
            if(count($valid_species_ids) <= 0) throw new \Exception("This item cannot create a pairing from the specieses of the chosen characters.");
 
            //create pairing
            $pairingData = [];
            $pairingData['user_id'] = $user->id;
            $pairingData['character_1_id'] = $character_1->id;
            $pairingData['character_2_id'] = $character_2->id;
            $pairingData['item_id'] = $item_id;

            //set approved if both chars are owned by the user
            if($character_1->user_id == $user->id) $pairingData["character_1_approved"] = 1;
            if($character_2->user_id == $user->id) $pairingData["character_2_approved"] = 1;
            if($character_1->user_id == $user->id && $character_2->user_id == $user->id) $pairingData['status'] = 'READY';


            $pairing = Pairing::create($pairingData);

            if(!$pairing) throw new \Exception("Error happened while trying to create pairing.");


            //remove pairing item
            if((new InventoryManager)->debitStack($user, 'Pairing Created', ['data' => ''], UserItem::where('item_id', $item_id)->first(), 1)){
                return $this->commitReturn($pairing);
            } else {
                throw new \Exception("Error happened while trying to remove the item.");
            }

        } catch(\Exception $e) {
            $this->setError('error', $e->getMessage());
        }
        return $this->rollbackReturn(false);
    }

    /**
     * Approve a pairing.
     *
     * @return \App\Models\Pairing\Pairing|bool
     */
    public function approvePairing($pairing_id, $user)
    {
        DB::beginTransaction();

        try {
            
            if(!isset($pairing_id)) throw new \Exception("Pairing Id must be set.");

            $pairing = Pairing::where('id', $pairing_id)->first();
            $character_1 = Character::where('id', $pairing->character_1_id)->first();
            $character_2 = Character::where('id', $pairing->character_2_id)->first();

            //set approval
            if($character_1->user_id == $user->id) $pairing->character_1_approved = 1;
            if($character_2->user_id == $user->id) $pairing->character_2_approved = 1;

            //update status
            if($pairing->character_1_approved == 1 && $pairing->character_2_approved == 1) $pairing->status = 'READY';

            //save pairing
            $pairing->save();

            if(!$pairing) throw new \Exception("Error happened while trying to approve pairing.");

            return $this->commitReturn($pairing);
        } catch(\Exception $e) {
            $this->setError('error', $e->getMessage());
        }
        return $this->rollbackReturn(false);
    }

    /**
     * Reject a pairing.
     *
     * @return \App\Models\Pairing\Pairing|bool
     */
    public function rejectPairing($pairing_id, $user)
    {
        DB::beginTransaction();

        try {
            
            if(!isset($pairing_id)) throw new \Exception("Pairing Id must be set.");

            $pairing = Pairing::where('id', $pairing_id)->first();
            $character_1 = Character::where('id', $pairing->character_1_id)->first();
            $character_2 = Character::where('id', $pairing->character_2_id)->first();

            //set approval
            if($character_1->user_id == $user->id) $pairing->character_1_approved = 0;
            if($character_2->user_id == $user->id) $pairing->character_2_approved = 0;

            //update status
            if($character_1->user_id == $user->id || $character_2->user_id == $user->id) $pairing->status = 'REJECTED';

            //save pairing
            $pairing->save();

            if(!$pairing) throw new \Exception("Error happened while trying to reject pairing.");

            return $this->commitReturn($pairing);
        } catch(\Exception $e) {
            $this->setError('error', $e->getMessage());
        }
        return $this->rollbackReturn(false);
    }


    /**
     * Create MYO
     *
     * @return \App\Models\Pairing\Pairing|bool
     */
    public function createMyo($pairing_id, $user)
    {
        DB::beginTransaction();

        try {
            
            if(!isset($pairing_id)) throw new \Exception("Pairing Id must be set.");

            $pairing = Pairing::where('id', $pairing_id)->first();
            $item = Item::where('id', $pairing->item_id)->first();
            $character_1 = Character::where('id', $pairing->character_1_id)->first();
            $character_2 = Character::where('id', $pairing->character_2_id)->first();
            $species_1_id = $character_1->image->species->id;
            $species_2_id = $character_2->image->species->id;
            $tag = $item->tag('pairing');
            
            if(!isset($tag)) throw new \Exception("Item is missing the required pairing tag.");
            $feature = Feature::where('id',  $tag->getData()["feature_id"])->first();
            $specieses = (isset($tag->getData()["species_ids"])) ? $tag->getData()["species_ids"] : null;
            $pairing_type = $tag->getData()["pairing_type"];

            $species_id = $this->getSpeciesId($specieses, $species_1_id, $species_2_id);
            $subtype_id = $this->getSubtypeId($species_id, $species_1_id, $species_2_id, $character_1, $character_2);
            $feature_pool = $this->getFeaturePool($character_1, $character_2, $species_id);
        
            
            $chosen_features = $this->getChosenFeatures($feature, $character_1, $character_2, $feature_pool);
            $feature_data = $this->getFeatureData($species_id, $subtype_id, $species_1_id, $species_2_id, $character_1, $character_2, $feature_pool, $chosen_features);

            $rarity_id = $this->getRarityId($feature, $character_1, $character_2, $feature_pool, $chosen_features);

            //create MYO
            $myo = $this->saveMyo($user, $species_id , $subtype_id, $rarity_id, array_unique($chosen_features), $feature_data);
            if(!$myo) throw new \Exception("Could not create MYO slot.");

            //update status
            $pairing->status = 'USED';
            $pairing->save();

            if(!$pairing) throw new \Exception("Error happened while trying to create a MYO from the pairing.");

            return $this->commitReturn($pairing);
        } catch(\Exception $e) {
            $this->setError('error', $e->getMessage());
        }
        return $this->rollbackReturn(false);
    }


    private function getFeaturePool($character_1, $character_2, $species_id){
        $all_feature_ids = array_merge($character_1->image->features()->pluck("feature_id")->toArray(), $character_2->image->features()->pluck("feature_id")->toArray());

        // get all features from the parents, but make sure to remove all features granted by other pairing items
        $pairing_tags = ItemTag::where('tag', 'pairing')->get();
        $pairing_feature_ids = [];
        foreach($pairing_tags as $tag) {
            $pairing_feature_ids[] = $tag->getData()["feature_id"];
        }
        $features = Feature::where("species_id", $species_id)->orWhere("species_id", null)->get();
        $features_filtered=$features->whereIn("id", $all_feature_ids)->whereNotIn("id", $pairing_feature_ids);

        return $features_filtered;
    }


    private function getFeatureData($species_id, $subtype_id,  $species_1_id, $species_2_id, $character_1, $character_2, $feature_pool, $chosen_features){
        if($species_1_id == $species_2_id){
            //parents with the same species - subtype is 50:50 between parents
            if($character_1->image->subtype_id != $subtype_id) $pairing_feature_data = $character_1->image->subtype->name;
            if($character_2->image->subtype_id != $subtype_id) $pairing_feature_data = $character_2->image->subtype->name;

            //if same subtype do not add hybrid trait...
            if($character_1->image->subtype_id == $character_2->image->subtype_id){
                $feature_data = [];
            } else{
                $feature_data = [$pairing_feature_data];
            }
        } else {
            //subtype is the type of the parent whose species was chosen
            if($character_1->image->species->id == $species_id){
                $pairing_feature_data = $character_2->image->species->name;
            } 
            if($character_2->image->species->id == $species_id){
                $pairing_feature_data = $character_1->image->species->name;
            }
            $feature_data = [$pairing_feature_data];

        }

        //add all chosen features data as empty
        foreach($feature_pool as $feature) {
            if(in_array($feature->id, $chosen_features)){
                $feature_data[] = null;
            } 
        }

        return $feature_data;
    }


    private function getSubtypeId($species_id, $species_1_id, $species_2_id, $character_1, $character_2){
        if($species_1_id == $species_2_id){
            //parents with the same species - subtype is 50:50 between parents
            $subtypes = [$character_1->image->subtype_id, $character_2->image->subtype_id];
            $subtype_id = $subtypes[array_rand($subtypes)];
        } else {
            //subtype is the type of the parent whose species was chosen
            if($character_1->image->species->id == $species_id){
                $subtype_id = $character_1->image->subtype_id;
            } 
            if($character_2->image->species->id == $species_id){
                $subtype_id = $character_2->image->subtype_id;
            }
        }
        return $subtype_id;
    }


    private function getRarityId($feature, $character_1, $character_2, $feature_pool, $chosen_features){
        // if same subtype do not start with the pairing rarity
        if($character_1->image->subtype_id == $character_2->image->subtype_id){
            $rarity_sorts = [];
        } else{
            $rarity_sorts = [$feature->rarity->sort];
        }

        //add all chosen features to rarity ids
        foreach($feature_pool as $feature) {
            if(in_array($feature->id, $chosen_features)){
                $rarity = $feature->rarity;
                $rarity_sorts[] = $rarity->sort;
            } 
        }

        //WARNING this assumes the highest rarity has the highest sort number and sort 0 is the lowest
        $rarity_sort = count($rarity_sorts) > 0 ? max($rarity_sorts) : 0;
        return Rarity::where("sort", $rarity_sort)->first()->id;
    }

    
    private function getChosenFeatures($feature, $character_1, $character_2, $feature_pool){
        // if same subtype do not start with the pairing feature
        if($character_1->image->subtype_id == $character_2->image->subtype_id){
            $chosen_features = [];
        } else{
            $chosen_features = [$feature->id];
        }

        // 0-3 features will be picked at random
        $feature_number = rand(0,3);
        $feature_ids = $feature_pool->pluck('id')->toArray();

        // shuffle and pick
        shuffle($feature_ids);
        return array_merge($chosen_features, array_slice($feature_ids, 0, $feature_number));

    }


    private function getSpeciesId($specieses, $species_1_id, $species_2_id){
        $valid_species_ids = [];
        if($specieses == null) {
            $valid_species_ids = [$species_1_id, $species_2_id];
        } else {
            if(in_array($species_1_id, $specieses)) $valid_species_ids[] = $species_1_id;
            if(in_array($species_2_id, $specieses)) $valid_species_ids[] = $species_2_id;
        }
        // 50:50 chance of either char being chosen for species if there are 2 species valid for this item
        $species_id_index = array_rand($valid_species_ids);
        return $valid_species_ids[$species_id_index];
    }

    private function saveMyo($user, $species_id, $subtype_id, $rarity_id, $feature_ids, $feature_data){
        //set user who the slot belongs to
        $characterData['user_id'] = $user->id;
        //other vital data that is default
        $characterData['name'] = "Pairing Slot";
        $characterData['transferrable_at'] = null;
        $characterData['is_myo_slot'] = 1;
        $characterData['description'] = "A MYO slot created from a Pairing. All traits listed can be used without needing an extra item.";

        //this uses your default MYO slot image from the CharacterManager
        //see wiki page for documentation on adding a default image switch
        $characterData['use_cropper'] = 0;
        $characterData['x0'] = null;
        $characterData['x1'] = null;
        $characterData['y0'] = null;
        $characterData['y1'] = null;
        $characterData['image'] = null;
        $characterData['thumbnail'] = null;
        $characterData['artist_id'][0] = null;
        $characterData['artist_url'][0] = null;
        $characterData['designer_id'][0] = null;
        $characterData['designer_url'][0] = null;

        // permissions
        $characterData['is_sellable'] = true;
        $characterData['is_tradeable'] = true;
        $characterData['is_giftable'] = true;
        $characterData['is_visible'] = false;
        $characterData['sale_value'] = 0;

        //species info
        $characterData['species_id'] = $species_id;
        $characterData['subtype_id'] = isset($subtype_id) && $subtype_id ? $subtype_id : null;

        $characterData['feature_id'] = $feature_ids;
        $characterData['feature_data'] = $feature_data;
        $characterData['rarity_id'] = $rarity_id;

        // create slot
        $charService = new CharacterManager;
        $character = $charService->createCharacter($characterData, $user, true);
        return $character;
        /**if ($character = $charService->createCharacter($characterData, $user, true)) {
            flash('<a href="' . $character->url . '">MYO slot</a> created successfully.')->success();
        }
        else {
            throw new \Exception("Failed to create the slot.");
        }*/
    }
}