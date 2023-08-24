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
use App\Models\User\UserItem;
use App\Models\Pairing\Pairing;
use App\Models\Species\Species;
use App\Models\Species\Subtype;
use App\Models\Rarity;
use App\Models\Feature\Feature;
use App\Models\Feature\FeatureCategory;
use App\Models\Character\CharacterTransformation as Transformation;
use App\Models\Character\Character;
use App\Models\Character\CharacterFeature;
use App\Models\Item\Item;
use App\Models\Item\ItemTag;

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
    public function createPairing($character_1_code, $character_2_code, $stack_id, $stack_quantity, $user)
    {
        DB::beginTransaction();

        try {

            //check that an item is attached
            if(!isset($stack_id)) throw new \Exception("You must attach a pairing item.");

            $itemIds = UserItem::whereIn('id', $stack_id)->pluck('item_id');

            $attachedPairingItem = Item::with(['tags' => fn($query) => $query->where('tag', 'pairing')])
            ->whereHas('tags', fn ($query) => $query->where('tag', 'pairing'))->whereIn('id', $itemIds)->get();
            
            //check that exactly one valid pairing item is attached
            if($attachedPairingItem->count() != 1) throw new \Exception("Pairing item not set correctly. Make sure to pick exactly one pairing item.");
            
            $attachedBoostItems = Item::with(['tags' => fn($query) => $query->where('tag', 'boost')])
            ->whereHas('tags', fn ($query) => $query->where('tag', 'boost'))->whereIn('id', $itemIds)->get();

            $character_1 = Character::where('slug', $character_1_code)->first();
            $character_2 = Character::where('slug', $character_2_code)->first();

            //check cooldown if set to do so. 
            $cooldownDays = Settings::get('pairing_cooldown');
            if( $cooldownDays != 0){
                $pairingsCharacter1 = Pairing::where(function($query) use ($character_1){
                    $query->where('character_1_id', $character_1->id)
                    ->orWhere('character_2_id', $character_1->id);
                })->whereIn('status', ['READY', 'OPEN'])->where( 'created_at', '>', Carbon::now()->subDays($cooldownDays))->get();
                if(!$pairingsCharacter1->isEmpty()) throw new \Exception("Character 1 cannot be paired right now due to the pairing cooldown of ".$cooldownDays." days!");
                $pairingsCharacter2 = Pairing::where(function($query)use ($character_2){
                    $query->where('character_1_id', $character_2->id)
                    ->orWhere('character_2_id', $character_2->id);
                })->whereIn('status', ['READY', 'OPEN'])->where( 'created_at', '>', Carbon::now()->subDays($cooldownDays))->get();
                if(!$pairingsCharacter2->isEmpty()) throw new \Exception("Character 2 cannot be paired right now due to the pairing cooldown of ".$cooldownDays." days!");
            }

            //do further checks
            if($this->validatePairingBasics($character_1_code, $character_2_code, $attachedPairingItem->first()->id)){
                //create pairing
                $pairingData = [];
                $pairingData['user_id'] = $user->id;
                $pairingData['character_1_id'] = $character_1->id;
                $pairingData['character_2_id'] = $character_2->id;

                //set approved if both chars are owned by the user
                if($character_1->user_id == $user->id) $pairingData["character_1_approved"] = 1;
                if($character_2->user_id == $user->id) $pairingData["character_2_approved"] = 1;
                if($character_1->user_id == $user->id && $character_2->user_id == $user->id) $pairingData['status'] = 'READY';

                if($character_1->user_id != $user->id || $character_2->user_id != $user->id){
                    // Attach items to hold if one char belongs to a different user

                    if(isset($stack_id)) {
                        $userAssets = createAssetsArray();

                        foreach($stack_id as $id) {
                            $stack = UserItem::with('item')->find($id);
                            if(!$stack || $stack->user_id != $user->id) throw new \Exception("Invalid item selected.");
                            if(!isset($stack_quantity[$id])) throw new \Exception("Invalid quantity selected.");
                            $stack->pairing_count += $stack_quantity[$id];
                            $stack->save();
                            addAsset($userAssets, $stack, $stack_quantity[$id]);
                        }
                    }
                    $pairingData['data'] = json_encode([
                        'user' => Arr::only(getDataReadyAssets($userAssets), ['user_items'])                
                    ]);

                } else {
                    if(isset($stack_id)) {
                        $userAssets = createAssetsArray();
                        foreach($stack_id as $id) {
                            $stack = UserItem::with('item')->find($id);
                            addAsset($userAssets, $stack, $stack_quantity[$id]);
                        }
                    }
                    $pairingData['data'] = json_encode([
                        'user' => Arr::only(getDataReadyAssets($userAssets), ['user_items'])                
                    ]);

                    $inventoryManager = new InventoryManager;

                    //debit all items
                    if(isset($stack_id)) {
                        foreach($stack_id as $id) {
                            $stack = UserItem::with('item')->find($id);
                            if(!$inventoryManager->debitStack($user,'Pairing Created', ['data' => ''], $stack, $stack_quantity[$id])) throw new \Exception("Failed to create log for item stack.");
                        }
                    }
                }

                $pairing = Pairing::create($pairingData);
                if(!$pairing) throw new \Exception("Error happened while trying to create pairing.");

                //notify other users if approval is needed
                if($character_1->user_id != $user->id) {
                    $otherUser1 = User::find($character_1->user_id);
                    Notifications::create('PAIRING_NEW_APPROVAL', $otherUser1, [
                        'character_1_url' => $character_1->url,
                        'character_1_slug' => $character_1->slug,
                        'character_2_url' => $character_2->url,
                        'character_2_slug' => $character_2->slug                    
                    ]);
                }
                //only send one notif if the 2 chars belong to the same person
                if($character_1->user_id != $character_2->user_id && $character_2->user_id != $user->id) {
                    $otherUser2 = User::find($character_2->user_id);
                    Notifications::create('PAIRING_NEW_APPROVAL', $otherUser2, [
                        'character_1_url' => $character_1->url,
                        'character_1_slug' => $character_1->slug,
                        'character_2_url' => $character_2->url,
                        'character_2_slug' => $character_2->slug
                    ]);
                }

                return $this->commitReturn($pairing);
            } else {
                return $this->rollbackReturn(false);
            }  

        } catch(\Exception $e) {
            $this->setError('error', $e->getMessage());
        }
        return $this->rollbackReturn(false);
    }

    public function validatePairingBasics($character_1_code, $character_2_code, $item_id){
        try {

            
            if(!isset($character_1_code) || !isset($character_2_code)) throw new \Exception("Please enter two character codes.");
            if($character_1_code == $character_2_code) throw new \Exception("Pairings must be between two different characters.");

            $character_1 = Character::where('slug', $character_1_code)->first();
            $character_2 = Character::where('slug', $character_2_code)->first();

            if(!isset($character_1) || !isset($character_2_code)) throw new \Exception("Invalid Character set.");
            if(!isset($character_2) || !isset($character_2_code)) throw new \Exception("Invalid Character set.");

            $species_1_id = $character_1->image->species->id;
            $species_2_id = $character_2->image->species->id;
            $item = Item::where('id', $item_id)->first();
            $tag = $item->tag('pairing');
            $specieses = (isset($tag->getData()["legal_species_id"])) ? $tag->getData()["legal_species_id"] : null;
            $pairing_type = $tag->getData()["pairing_type"];

            if(!isset($tag)) throw new \Exception("Item is missing the required pairing tag.");

            //check sex if set to do so. If one char has no sex it always works.
            if(Settings::get('pairing_sex_restrictions') == 1){
                if(isset($character_1->image->sex) && isset($character_2->image->sex)){
                    if($character_1->image->sex == $character_2->image->sex)  throw new \Exception("Pairings can only be created between a male and female character.");
                }
            }

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
            return true;

        } catch(\Exception $e) {
            $this->setError('error', $e->getMessage());
        }
        return false;
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
            $pairingUser = User::find($pairing->user_id);
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

            // debit items used for pairing
            // Remove any added items, hold counts, and add logs
            $addonData = $pairing->data['user'];
            $inventoryManager = new InventoryManager;
            if(isset($addonData['user_items'])) {
                $stacks = $addonData['user_items'];
                foreach($addonData['user_items'] as $userItemId => $quantity) {
                    $userItemRow = UserItem::find($userItemId);
                    if(!$userItemRow) throw new \Exception("Cannot return an invalid item. (".$userItemId.")");
                    if($userItemRow->pairing_count < $quantity) throw new \Exception("Cannot return more items than was held. (".$userItemId.")");
                    $userItemRow->pairing_count -= $quantity;
                    $userItemRow->save();
                }

                foreach($stacks as $stackId=>$quantity) {
                    $stack = UserItem::find($stackId);
                    if(!$inventoryManager->debitStack($pairingUser, 'Pairing approved', ['data' => 'Item used in a pairing.'], $stack, $quantity)) throw new \Exception("Failed to create log for item stack.");
                }
            }

            // Notify the user
            Notifications::create('PAIRING_APPROVED', $pairingUser, [
                'character_1_url' => $character_1->url,
                'character_1_slug' => $character_1->slug,
                'character_2_url' => $character_2->url,
                'character_2_slug' => $character_2->slug            
            ]);

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
            $pairingUser = User::find($pairing->user_id);
            $character_1 = Character::where('id', $pairing->character_1_id)->first();
            $character_2 = Character::where('id', $pairing->character_2_id)->first();

            //set approval
            if($character_1->user_id == $user->id) $pairing->character_1_approved = 0;
            if($character_2->user_id == $user->id) $pairing->character_2_approved = 0;

            //update status
            if($character_1->user_id == $user->id || $character_2->user_id == $user->id) $pairing->status = 'REJECTED';
            if($pairing->user_id == $user->id) $pairing->status = 'REJECTED';

            //save pairing
            $pairing->save();

            if(!$pairing) throw new \Exception("Error happened while trying to reject pairing.");

            // Return all added items
            $addonData = $pairing->data['user'];
            if(isset($addonData['user_items'])) {
                foreach($addonData['user_items'] as $userItemId => $quantity) {
                    $userItemRow = UserItem::find($userItemId);
                    if(!$userItemRow) throw new \Exception("Cannot return an invalid item. (".$userItemId.")");
                    if($userItemRow->pairing_count < $quantity) throw new \Exception("Cannot return more items than was held. (".$userItemId.")");
                    $userItemRow->pairing_count -= $quantity;
                    $userItemRow->save();
                }
            }

            // Notify the user
            Notifications::create('PAIRING_REJECTED', $pairingUser, [
                'character_1_url' => $character_1->url,
                'character_1_slug' => $character_1->slug,
                'character_2_url' => $character_2->url,
                'character_2_slug' => $character_2->slug
            ]);

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
    public function createMyos($pairing_id, $user)
    {
        DB::beginTransaction();

        try {
            
            if(!isset($pairing_id)) throw new \Exception("Pairing Id must be set.");

            $pairing = Pairing::where('id', $pairing_id)->first();
            if(!$pairing->status == 'READY') throw new \Exception("Pairing is not approved yet.");

            $item = null;
            $boosts = [];

            $addonData = $pairing->data['user'];
            if(isset($addonData['user_items'])) {
                foreach($addonData['user_items'] as $userItemId => $quantity) {
                    $userItemRow = UserItem::find($userItemId);
                    if(!$userItemRow) throw new \Exception("Cannot return an invalid item. (".$userItemId.")");
                    if($userItemRow->item()->first()->tag('pairing') != null) $item = $userItemRow->item()->first();
                    if($userItemRow->item()->first()->tag('boost') != null) $boosts[] = $userItemRow->item()->first();
                }
            }

            $character_1 = Character::where('id', $pairing->character_1_id)->first();
            $character_2 = Character::where('id', $pairing->character_2_id)->first();
            $species_1_id = $character_1->image->species->id;
            $species_2_id = $character_2->image->species->id;
            $tag = $item->tag('pairing');
            if(!isset($tag)) throw new \Exception("Item is missing the required pairing tag.");
            $myoAmount = random_int($tag->getData()["min"], $tag->getData()["max"]);
            $pairing_type = $tag->getData()["pairing_type"];

            //loop over for each myo
            for($i = 0; $i < $myoAmount; $i++){
                $sex = $this->getSex($boosts);
                $species_id = $this->getSpeciesId($tag, $pairing_type, $species_1_id, $species_2_id);
                $subtype_id = $this->getSubtypeId($species_id, $species_1_id, $species_2_id, $character_1, $character_2);

                $feature_pool = $this->getFeaturePool($tag, $character_1, $character_2, $species_id, $boosts);

                $chosen_features = $this->getChosenFeatures($tag, $character_1, $character_2, $feature_pool, $boosts);
                $feature_data = $this->getFeatureData($tag, $species_id, $subtype_id, $species_1_id, $species_2_id, $character_1, $character_2, $chosen_features);
                $rarity_id = $this->getRarityId($character_1, $character_2, $chosen_features);

                //create MYO
                $myo = $this->saveMyo($user, $sex, $species_id , $subtype_id, $rarity_id, array_unique(array_keys($chosen_features)), $feature_data);

                if(!$myo) throw new \Exception("Could not create MYO slot.");
            }

            //update status
            $pairing->status = 'USED';
            $pairing->save();

            if(!$pairing) throw new \Exception("Error happened while trying to create a MYO from the pairing.");
            $this->commitReturn($pairing);
            return $myoAmount;
        } catch(\Exception $e) {
            $this->setError('error', $e->getMessage());
        }
        return $this->rollbackReturn(false);
    }

    /**
     * Rolls Test MYOs without saving them.
     *
     * @return \App\Models\Pairing\Pairing|bool
     */
    public function rollTestMyos($character_1_code, $character_2_code, $item_ids, $user)
    {

        try {

            $item = Item::with(['tags' => fn($query) => $query->where('tag', 'pairing')])
            ->whereHas('tags', fn ($query) => $query->where('tag', 'pairing'))->whereIn('id', $item_ids)->get();
            
            //check that exactly one valid pairing item is attached
            if($item->count() != 1) throw new \Exception("Pairing item not set correctly. Make sure to pick exactly one pairing item.");
            
            $boosts = Item::with(['tags' => fn($query) => $query->where('tag', 'boost')])
            ->whereHas('tags', fn ($query) => $query->where('tag', 'boost'))->whereIn('id', $item_ids)->get();

            $testMyos = [];

            if($this->validatePairingBasics($character_1_code, $character_2_code, $item->first()->id)){
                $character_1 = Character::where('slug', $character_1_code)->first();
                $character_2 = Character::where('slug', $character_2_code)->first();
                $species_1_id = $character_1->image->species->id;
                $species_2_id = $character_2->image->species->id;
                $tag = $item->first()->tag('pairing');
                if(!isset($tag)) throw new \Exception("Item is missing the required pairing tag.");
                $myoAmount = random_int($tag->getData()["min"], $tag->getData()["max"]);
                $pairing_type = $tag->getData()["pairing_type"];

                //loop over for each myo
                for($i = 0; $i < $myoAmount; $i++){
                    $sex = $this->getSex($boosts);
                    $species_id = $this->getSpeciesId($tag, $pairing_type, $species_1_id, $species_2_id);
                    $subtype_id = $this->getSubtypeId($species_id, $species_1_id, $species_2_id, $character_1, $character_2);

                    $feature_pool = $this->getFeaturePool($tag, $character_1, $character_2, $species_id,$boosts);

                    $chosen_features = $this->getChosenFeatures($tag, $character_1, $character_2, $feature_pool, $boosts);

                    $feature_data = $this->getFeatureData($tag, $species_id, $subtype_id, $species_1_id, $species_2_id, $character_1, $character_2, $chosen_features);
                    $rarity_id = $this->getRarityId($character_1, $character_2, $chosen_features);

                    $testMyos[] = [
                        'user' => $user,
                        'sex' => $sex,
                        'species' => Species::where('id', $species_id)->first()->name,
                        'subtype' => Subtype::where('id', $subtype_id)->first()?->name,
                        'rarity' => Rarity::where('id', $rarity_id)->first()->name,
                        'features' => $chosen_features,
                        'feature_data' => $feature_data
                    ];
                }
                return $testMyos;
            } else {
                return null;
            }  
        } catch(\Exception $e) {
            $this->setError('error', $e->getMessage());
        }
    }


    private function getSex($boosts){
        $maleBoostPercentage = null;
        $femaleBoostPercentage = null;
        $malePercentage = Settings::get('pairing_male_percentage');
        $femalePercentage = Settings::get('pairing_female_percentage');

        foreach($boosts as $boost){
            if($boost->tag('boost') != null && isset($boost->tag('boost')->getData()['setting'])){
                if($boost->tag('boost')->getData()['setting']  == 'pairing_male_percentage') $maleBoostPercentage = $boost->tag('boost')->getData()["setting_chance"];
                if($boost->tag('boost')->getData()['setting']  == 'pairing_female_percentage') $femaleBoostPercentage = $boost->tag('boost')->getData()["setting_chance"];
            }
        }
        
        //sex is disabled in site settings
        if($malePercentage == 0 && $femalePercentage == 0) return null;

        //prioritize boosts
        if(isset($maleBoostPercentage)){
            return (random_int(0,100) <= $maleBoostPercentage) ? 'Male' : 'Female';
        }
        if(isset($femaleBoostPercentage)){
            return (random_int(0,100) <= $femaleBoostPercentage) ? 'Female' : 'Male';
        }

        //otherwise use settings
        if($malePercentage + $femalePercentage == 100){
            return (random_int(0,100) <= $femalePercentage) ? 'Female' : 'Male';
        } else {
            throw new \Exception("Male and female chance is not set to a total of 100. Please contact a mod/admin."); 
        }
        
    }

    private function getFeaturePool($tag, $character_1, $character_2, $species_id, $boosts){
        $inheritBothBoostPercentage = null;

        foreach($boosts as $boost){
            if($boost->tag('boost') != null && isset($boost->tag('boost')->getData()['setting'])){
                if($boost->tag('boost')->getData()['setting']  == 'pairing_trait_inheritance') $inheritBothBoostPercentage = $boost->tag('boost')->getData()["setting_chance"];
            }
        }

        $inheritFromBothParentsChance = (isset($inheritBothBoostPercentage)) ? $inheritBothBoostPercentage : Settings::get('pairing_trait_inheritance');
        if(random_int(0,100) <= $inheritFromBothParentsChance){
            //inherit traits from both parents
            $all_feature_ids = array_merge($character_1->image->features()->pluck("feature_id")->toArray(), $character_2->image->features()->pluck("feature_id")->toArray());
        } else {
            //randomly pick 1 parent to inherit traits from
            $featureArrays = [$character_1->image->features()->pluck("feature_id")->toArray(), $character_2->image->features()->pluck("feature_id")->toArray()];
            $randomKey = array_rand($featureArrays);
            $all_feature_ids = $featureArrays[$randomKey];
        }


        // get all features from the parents, but make sure to remove all features granted by other pairing items
        $pairing_tags = ItemTag::where('tag', 'pairing')->get();
        $pairing_feature_ids = [];
        foreach($pairing_tags as $pairingTag) {
            if(isset($pairingTag->getData()["feature_id"])) $pairing_feature_ids[] = $pairingTag->getData()["feature_id"];
        }

        // if species from tag is used, allow all features regardless of species
        if(isset($tag->getData()['species_id'])){
            $features = Feature::get();
        } else {
            $features = Feature::where("species_id", $species_id)->orWhere("species_id", null)->get();
        }

        // if legal features were set, only include those.
        $legal_feature_ids = (isset($tag->getData()["legal_feature_id"])) ? $tag->getData()["legal_feature_id"] : null;
        if($legal_feature_ids){
            $features_filtered=$features->whereIn("id", $all_feature_ids)->whereIn("id", $legal_feature_ids)->whereNotIn("id", $pairing_feature_ids);
        } else {
            $features_filtered=$features->whereIn("id", $all_feature_ids)->whereNotIn("id", $pairing_feature_ids);
        }

        return $features_filtered;
    }


    private function getFeatureData($tag, $species_id, $subtype_id,  $species_1_id, $species_2_id, $character_1, $character_2, $chosen_features){
        $pairing_feature_data = null;
        if($species_1_id == $species_2_id){
            //parents with the same species - subtype is 50:50 between parents
            if($character_1->image->subtype_id != $subtype_id) $pairing_feature_data = $character_1->image->subtype->name;
            if($character_2->image->subtype_id != $subtype_id) $pairing_feature_data = $character_2->image->subtype->name;
        } else {
            //subtype is the type of the parent whose species was chosen
            if($character_1->image->species->id == $species_id){
                $pairing_feature_data = $character_2->image->species->name;
            } 
            elseif($character_2->image->species->id == $species_id){
                $pairing_feature_data = $character_1->image->species->name;
            } else {
                //if a species was chosen in the item tag, set feature data to nothing.
                $pairing_feature_data = null;
            }
        }

        $feature_data = [];
        //add all chosen features data as empty
        foreach($chosen_features as $featureId=>$feature){
            if(isset($tag->getData()["feature_id"]) && $featureId == $tag->getData()["feature_id"]){
                // set subtype/species of other parent if a trait was set for it.
                $feature_data[] = $pairing_feature_data;
            } else {
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
            elseif($character_2->image->species->id == $species_id){
                $subtype_id = $character_2->image->subtype_id;
            } else {
                //in case a species was set for inheritance, we set subtype as null/open for choice.
                $subtype_id = null;
            }
        }
        return $subtype_id;
    }


    private function getRarityId($character_1, $character_2, $chosen_features){
        $rarity_sorts = [];
        //add all chosen features to rarity ids
        foreach($chosen_features as $feature) {
            $rarity = $feature->rarity;
            $rarity_sorts[] = $rarity->sort;
        }

        //WARNING this assumes the highest rarity has the highest sort number and sort 0 is the lowest
        $rarity_sort = count($rarity_sorts) > 0 ? max($rarity_sorts) : 0;
        return Rarity::where("sort", $rarity_sort)->first()->id;
    }

    
    private function getChosenFeatures($tag, $character_1, $character_2, $feature_pool, $boosts){

        $boostedChanceByRarity = [];
        // sort boosts by rarityid for easier access
        foreach($boosts as $boost){
            if($boost->tag('boost') != null){
                $boostTag = $boost->tag('boost');
                if(isset($boostTag->getData()['rarity_id'])){
                    $boostedChanceByRarity[$boostTag->getData()['rarity_id']] = $boostTag->getData()['rarity_chance'];
                }
            }
        }

        //sort features by category
        $featuresByCategory = $feature_pool->groupBy('feature_category_id');
        $chosenFeatures = [];
        foreach($featuresByCategory as $categoryId=>$features){
            $features = $features->shuffle();
            $category = FeatureCategory::where('id', $categoryId)->first();
            $featuresCalculated = 0;
            $featuresChosen = 0;
            $i = 0;
            //loop over features until min amount is chosen but never more than max amount
            while(($featuresCalculated < count($features) && $featuresChosen < $category->max_inheritable) || ($featuresChosen < $category->min_inheritable)){
                $feature = $features[$i];
                $inheritChance = (isset($boostedChanceByRarity[$feature->rarity->id])) ? $boostedChanceByRarity[$feature->rarity->id] : $feature->rarity->inherit_chance;
                //calc inheritance chance
                $doesGetThisFeature = (random_int(0,100) <= $inheritChance) ? true : false;                    
                if($doesGetThisFeature) {
                    $chosenFeatures[$feature->id] = $feature;
                    $featuresChosen += 1;
                }
                $featuresCalculated += 1;
                $i = ($i === count($features) - 1) ? 0 : $i++;
            }
        }
        //set pairing feature
        if(isset($tag->getData()["feature_id"]) && $character_1->image->subtype_id != $character_2->image->subtype_id){
            $pairingFeature = Feature::where("id", $tag->getData()["feature_id"])->first();
            $chosenFeatures[$pairingFeature->id] = $pairingFeature;
        }

        return $chosenFeatures;

    }


    private function getSpeciesId($tag, $pairing_type, $species_1_id, $species_2_id){
        // if a species was set for the item, it should be the item...
        
        //pairing type 0 = species, 1 = subtype
        if(isset($tag->getData()['species_id'])) return $tag->getData()['species_id'];
       
        $specieses = (isset($tag->getData()["legal_species_id"])) ? $tag->getData()["legal_species_id"] : null;
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

    private function saveMyo($user, $sex, $species_id, $subtype_id, $rarity_id, $feature_ids, $feature_data){
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
        $characterData['sex'] = $sex;
        $characterData['species_id'] = $species_id;
        $characterData['subtype_id'] = isset($subtype_id) && $subtype_id ? $subtype_id : null;

        $characterData['feature_id'] = $feature_ids;
        $characterData['feature_data'] = $feature_data;
        $characterData['rarity_id'] = $rarity_id;

        // create slot
        $charService = new CharacterManager;
        $character = $charService->createCharacter($characterData, $user, true);
        return $character;
    }
}