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
    public function createPairing($character1Code, $character2Code, $stackId, $stackQuantity, $user)
    {
        DB::beginTransaction();

        try {

            //check that an item is attached
            if(!isset($stackId)) throw new \Exception("You must attach a pairing item.");

            $itemIds = UserItem::whereIn('id', $stackId)->pluck('item_id');

            $attachedPairingItem = Item::with(['tags' => fn($query) => $query->where('tag', 'pairing')])
            ->whereHas('tags', fn ($query) => $query->where('tag', 'pairing'))->whereIn('id', $itemIds)->get();
            
            //check that exactly one valid pairing item is attached
            if($attachedPairingItem->count() != 1) throw new \Exception("Pairing item not set correctly. Make sure to pick exactly one pairing item.");
            
            $attachedBoostItems = Item::with(['tags' => fn($query) => $query->where('tag', 'boost')])
            ->whereHas('tags', fn ($query) => $query->where('tag', 'boost'))->whereIn('id', $itemIds)->get();

            $character1 = Character::where('slug', $character1Code)->first();
            $character2 = Character::where('slug', $character2Code)->first();

            //check cooldown if set to do so. 
            $cooldownDays = Settings::get('pairing_cooldown');
            if( $cooldownDays != 0){
                $pairingsCharacter1 = Pairing::where(function($query) use ($character1){
                    $query->where('character_1_id', $character1->id)
                    ->orWhere('character_2_id', $character1->id);
                })->whereIn('status', ['READY', 'OPEN'])->where( 'created_at', '>', Carbon::now()->subDays($cooldownDays))->get();
                if(!$pairingsCharacter1->isEmpty()) throw new \Exception("Character 1 cannot be paired right now due to the pairing cooldown of ".$cooldownDays." days!");
                $pairingsCharacter2 = Pairing::where(function($query)use ($character2){
                    $query->where('character_1_id', $character2->id)
                    ->orWhere('character_2_id', $character2->id);
                })->whereIn('status', ['READY', 'OPEN'])->where( 'created_at', '>', Carbon::now()->subDays($cooldownDays))->get();
                if(!$pairingsCharacter2->isEmpty()) throw new \Exception("Character 2 cannot be paired right now due to the pairing cooldown of ".$cooldownDays." days!");
            }

            //do further checks
            if($this->validatePairingBasics($character1Code, $character2Code, $attachedPairingItem->first()->id)){
                //create pairing
                $pairingData = [];
                $pairingData['user_id'] = $user->id;
                $pairingData['character_1_id'] = $character1->id;
                $pairingData['character_2_id'] = $character2->id;

                //set approved if both chars are owned by the user
                if($character1->user_id == $user->id) $pairingData["character_1_approved"] = 1;
                if($character2->user_id == $user->id) $pairingData["character_2_approved"] = 1;
                if($character1->user_id == $user->id && $character2->user_id == $user->id) $pairingData['status'] = 'READY';

                if($character1->user_id != $user->id || $character2->user_id != $user->id){
                    // Attach items to hold if one char belongs to a different user

                    if(isset($stackId)) {
                        $userAssets = createAssetsArray();

                        foreach($stackId as $id) {
                            $stack = UserItem::with('item')->find($id);
                            if(!$stack || $stack->user_id != $user->id) throw new \Exception("Invalid item selected.");
                            if(!isset($stackQuantity[$id])) throw new \Exception("Invalid quantity selected.");
                            $stack->pairing_count += $stackQuantity[$id];
                            $stack->save();
                            addAsset($userAssets, $stack, $stackQuantity[$id]);
                        }
                    }
                    $pairingData['data'] = json_encode([
                        'user' => Arr::only(getDataReadyAssets($userAssets), ['user_items'])                
                    ]);

                } else {
                    if(isset($stackId)) {
                        $userAssets = createAssetsArray();
                        foreach($stackId as $id) {
                            $stack = UserItem::with('item')->find($id);
                            addAsset($userAssets, $stack, $stackQuantity[$id]);
                        }
                    }
                    $pairingData['data'] = json_encode([
                        'user' => Arr::only(getDataReadyAssets($userAssets), ['user_items'])                
                    ]);

                    $inventoryManager = new InventoryManager;

                    //debit all items
                    if(isset($stackId)) {
                        foreach($stackId as $id) {
                            $stack = UserItem::with('item')->find($id);
                            if(!$inventoryManager->debitStack($user,'Pairing Created', ['data' => ''], $stack, $stackQuantity[$id])) throw new \Exception("Failed to create log for item stack.");
                        }
                    }
                }

                $pairing = Pairing::create($pairingData);
                if(!$pairing) throw new \Exception("Error happened while trying to create pairing.");

                //notify other users if approval is needed
                if($character1->user_id != $user->id) {
                    $otherUser1 = User::find($character1->user_id);
                    Notifications::create('PAIRING_NEW_APPROVAL', $otherUser1, [
                        'character_1_url' => $character1->url,
                        'character_1_slug' => $character1->slug,
                        'character_2_url' => $character2->url,
                        'character_2_slug' => $character2->slug                    
                    ]);
                }
                //only send one notif if the 2 chars belong to the same person
                if($character1->user_id != $character2->user_id && $character2->user_id != $user->id) {
                    $otherUser2 = User::find($character2->user_id);
                    Notifications::create('PAIRING_NEW_APPROVAL', $otherUser2, [
                        'character_1_url' => $character1->url,
                        'character_1_slug' => $character1->slug,
                        'character_2_url' => $character2->url,
                        'character_2_slug' => $character2->slug
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

    public function validatePairingBasics($character1Code, $character2Code, $item_id){
        try {

            
            if(!isset($character1Code) || !isset($character2Code)) throw new \Exception("Please enter two character codes.");
            if($character1Code == $character2Code) throw new \Exception("Pairings must be between two different characters.");

            $character1 = Character::where('slug', $character1Code)->first();
            $character2 = Character::where('slug', $character2Code)->first();

            if(!isset($character1) || !isset($character2Code)) throw new \Exception("Invalid Character set.");
            if(!isset($character2) || !isset($character2Code)) throw new \Exception("Invalid Character set.");

            $item = Item::where('id', $item_id)->first();
            $tag = $item->tag('pairing');

            if(!isset($tag)) throw new \Exception("Item is missing the required pairing tag.");
            $species1Id = $character1->image->species->id;
            $species2Id = $character2->image->species->id;

            //check sex if set to do so. If one char has no sex it always works.
            if(Settings::get('pairing_sex_restrictions') == 1){
                if(isset($character1->image->sex) && isset($character2->image->sex)){
                    if($character1->image->sex == $character2->image->sex)  throw new \Exception("Pairings can only be created between a male and female character.");
                }
            }

            //check if the pairing type matches the character input
            //pairing type 0 = species, 1 = subtype
            $pairingType = (isset($tag->getData()["pairing_type"])) ? $tag->getData()["pairing_type"] : null;
            if(isset($pairingType)){
                if($pairingType == 1 && $species1Id != $species2Id ) throw new \Exception("A subtype pairing can only be done with characters of the same species.");
                if($pairingType == 0 && $species1Id == $species2Id ) throw new \Exception("A species pairing can only be done with characters of different species.");
            }


            // check if correct species was used for the characters
            $illegalSpecieses = (isset($tag->getData()["illegal_species_id"])) ? $tag->getData()["illegal_species_id"] : null;
            $validSpeciesIds = [];
            if($illegalSpecieses == null) {
                $validSpeciesIds = [$species1Id, $species2Id];
            } else {
                if(!in_array($species1Id, $illegalSpecieses)) $validSpeciesIds[] = $species1Id;
                if(!in_array($species2Id, $illegalSpecieses)) $validSpeciesIds[] = $species2Id;
            }
            if(count($validSpeciesIds) <= 0 && !isset($tag->getData()["default_species_id"])) throw new \Exception("This item cannot create a pairing from the specieses of the chosen characters.");

            // check if correct subtypes were used for the characters
            $illegalSubtypes = (isset($tag->getData()["illegal_subtype_id"])) ? $tag->getData()["illegal_subtype_id"] : null;
            $sub1Id = $character1->image->subtype?->id;
            $sub2Id = $character2->image->subtype?->id;
            $validSubtypeIds = [];
            if($illegalSubtypes == null) {
                $validSubtypeIds = [$sub1Id, $sub2Id];
            } else {
                if(!in_array($sub1Id, $illegalSubtypes)) $validSubtypeIds[] = $sub1Id;
                if(!in_array($sub2Id, $illegalSubtypes)) $validSubtypeIds[] = $sub2Id;
            }
            if(count($validSubtypeIds) <= 0 && !isset($tag->getData()["default_subtype_id"])) throw new \Exception("This item cannot create a pairing from the subtypes of the chosen characters.");

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
            $character1 = Character::where('id', $pairing->character_1_id)->first();
            $character2 = Character::where('id', $pairing->character_2_id)->first();

            //set approval
            if($character1->user_id == $user->id) $pairing->character_1_approved = 1;
            if($character2->user_id == $user->id) $pairing->character_2_approved = 1;

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
                'character_1_url' => $character1->url,
                'character_1_slug' => $character1->slug,
                'character_2_url' => $character2->url,
                'character_2_slug' => $character2->slug            
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
            $character1 = Character::where('id', $pairing->character_1_id)->first();
            $character2 = Character::where('id', $pairing->character_2_id)->first();

            //set approval
            if($character1->user_id == $user->id) $pairing->character_1_approved = 0;
            if($character2->user_id == $user->id) $pairing->character_2_approved = 0;

            //update status
            if($character1->user_id == $user->id || $character2->user_id == $user->id) $pairing->status = 'REJECTED';
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
                'character_1_url' => $character1->url,
                'character_1_slug' => $character1->slug,
                'character_2_url' => $character2->url,
                'character_2_slug' => $character2->slug
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

            $character1 = Character::where('id', $pairing->character_1_id)->first();
            $character2 = Character::where('id', $pairing->character_2_id)->first();
            $species1 = $character1->image->species;
            $species2 = $character2->image->species;
            $tag = $item->tag('pairing');
            if(!isset($tag)) throw new \Exception("Item is missing the required pairing tag.");
            $myoAmount = random_int($tag->getData()["min"], $tag->getData()["max"]);

            //loop over for each myo
            for($i = 0; $i < $myoAmount; $i++){
                $sex = $this->getSex($boosts);
                $speciesId = $this->getSpeciesId($tag, $species1, $species2);
                $subtypeId = $this->getSubtypeId($tag, $speciesId, $species1->id, $species2->id, $character1, $character2);
                $featurePool = $this->getFeaturePool($tag, $character1, $character2, $speciesId, $subtypeId, $boosts);
                $chosenFeatures = $this->getChosenFeatures($tag, $character1, $character2, $featurePool, $boosts);
                $featureData = $this->getFeatureData($tag, $speciesId, $subtypeId, $species1->id, $species2->id, $character1, $character2, $chosenFeatures);
                $rarityId = $this->getRarityId($character1, $character2, $chosenFeatures);

                //create MYO
                $myo = $this->saveMyo($user, $sex, $speciesId , $subtypeId, $rarityId, array_unique(array_keys($chosenFeatures)), $featureData, $character1->slug, $character2->slug);

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
    public function rollTestMyos($character1Code, $character2Code, $item_ids, $user)
    {

        try {

            $item = Item::with(['tags' => fn($query) => $query->where('tag', 'pairing')])
            ->whereHas('tags', fn ($query) => $query->where('tag', 'pairing'))->whereIn('id', $item_ids)->get();
            
            //check that exactly one valid pairing item is attached
            if($item->count() != 1) throw new \Exception("Pairing item not set correctly. Make sure to pick exactly one pairing item.");
            
            $boosts = Item::with(['tags' => fn($query) => $query->where('tag', 'boost')])
            ->whereHas('tags', fn ($query) => $query->where('tag', 'boost'))->whereIn('id', $item_ids)->get();

            $testMyos = [];

            if($this->validatePairingBasics($character1Code, $character2Code, $item->first()->id)){
                $character1 = Character::where('slug', $character1Code)->first();
                $character2 = Character::where('slug', $character2Code)->first();
                $species1 = $character1->image->species;
                $species2 = $character2->image->species;
                $tag = $item->first()->tag('pairing');
                if(!isset($tag)) throw new \Exception("Item is missing the required pairing tag.");
                $myoAmount = random_int($tag->getData()["min"], $tag->getData()["max"]);

                //loop over for each myo
                for($i = 0; $i < $myoAmount; $i++){
                    $sex = $this->getSex($boosts);
                    $speciesId = $this->getSpeciesId($tag, $species1, $species2);
                    $subtypeId = $this->getSubtypeId($tag, $speciesId, $species1->id, $species2->id, $character1, $character2);
                    $featurePool = $this->getFeaturePool($tag, $character1, $character2, $speciesId, $subtypeId, $boosts);
                    $chosenFeatures = $this->getChosenFeatures($tag, $character1, $character2, $featurePool, $boosts);
                    $featureData = $this->getFeatureData($tag, $speciesId, $subtypeId, $species1->id, $species2->id, $character1, $character2, $chosenFeatures);
                    $rarityId = $this->getRarityId($character1, $character2, $chosenFeatures);
                    $testMyos[] = [
                        'user' => $user,
                        'sex' => $sex,
                        'species' => Species::where('id', $speciesId)->first()->name,
                        'subtype' => Subtype::where('id', $subtypeId)->first()?->name,
                        'rarity' => Rarity::where('id', $rarityId)->first()->name,
                        'features' => $chosenFeatures,
                        'feature_data' => $featureData
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

    private function getFeaturePool($tag, $character1, $character2, $speciesId, $subtypeId, $boosts){
        $inheritBothBoostPercentage = null;

        foreach($boosts as $boost){
            if($boost->tag('boost') != null && isset($boost->tag('boost')->getData()['setting'])){
                if($boost->tag('boost')->getData()['setting']  == 'pairing_trait_inheritance') $inheritBothBoostPercentage = $boost->tag('boost')->getData()["setting_chance"];
            }
        }

        $inheritFromBothParentsChance = (isset($inheritBothBoostPercentage)) ? $inheritBothBoostPercentage : Settings::get('pairing_trait_inheritance');
        if(random_int(0,100) <= $inheritFromBothParentsChance){
            //inherit traits from both parents
            $allFeatureIds = array_merge($character1->image->features()->pluck("feature_id")->toArray(), $character2->image->features()->pluck("feature_id")->toArray());
        } else {
            //randomly pick 1 parent to inherit traits from
            $featureArrays = [$character1->image->features()->pluck("feature_id")->toArray(), $character2->image->features()->pluck("feature_id")->toArray()];
            $randomKey = array_rand($featureArrays);
            $allFeatureIds = $featureArrays[$randomKey];
        }


        // get all features from the parents, but make sure to remove all features granted by other pairing items
        $pairingTags = ItemTag::where('tag', 'pairing')->get();
        $pairingFeatureIds = [];
        foreach($pairingTags as $pairingTag) {
            if(isset($pairingTag->getData()["feature_id"])) $pairingFeatureIds[] = $pairingTag->getData()["feature_id"];
        }

        // if species from tag is used, allow all features regardless of species
        if(isset($tag->getData()['species_id'])){
            $features = Feature::get();
        } else {
            //filter features that are valid for the chosen species and subtype
            $features = Feature::where(function($query) use ($speciesId){
                $query->where('species_id', $speciesId)
                ->orWhere('species_id', null);
            })->where(function($query) use ($subtypeId){
                $query->where('subtype_id', $subtypeId)
                ->orWhere('subtype_id', null);
            })->get();
        }

        // if illegal features were set, do not include those.
        $illegalFeatureIds = (isset($tag->getData()["illegal_feature_id"])) ? $tag->getData()["illegal_feature_id"] : null;
        if($illegalFeatureIds){
            $featuresFiltered=$features->whereIn("id", $allFeatureIds)->whereNotIn("id", $illegalFeatureIds)->whereNotIn("id", $pairingFeatureIds);
        } else {
            $featuresFiltered=$features->whereIn("id", $allFeatureIds)->whereNotIn("id", $pairingFeatureIds);
        }

        return $featuresFiltered;
    }


    private function getFeatureData($tag, $speciesId, $subtypeId,  $species1Id, $species2Id, $character1, $character2, $chosenFeatures){
        $pairingFeatureData = null;
        if($species1Id == $species2Id){
            //parents with the same species - subtype is 50:50 between parents
            if($character1->image->subtype_id != $subtypeId) $pairingFeatureData = $character1->image->subtype?->name;
            if($character2->image->subtype_id != $subtypeId) $pairingFeatureData = $character2->image->subtype?->name;
        } else {
            //subtype is the type of the parent whose species was chosen
            if($character1->image->species->id == $speciesId){
                $pairingFeatureData = $character2->image->species->name;
            } 
            elseif($character2->image->species->id == $speciesId){
                $pairingFeatureData = $character1->image->species->name;
            } else {
                //if a species was chosen in the item tag, set feature data to nothing.
                $pairingFeatureData = null;
            }
        }

        $featureData = [];
        //add all chosen features data as empty
        foreach($chosenFeatures as $featureId=>$feature){
            if(isset($tag->getData()["feature_id"]) && $featureId == $tag->getData()["feature_id"]){
                // set subtype/species of other parent if a trait was set for it.
                $featureData[] = $pairingFeatureData;
            } else {
                $featureData[] = null;
            }
        }
        return $featureData;
    }


    private function getSubtypeId($tag, $speciesId, $species1Id, $species2Id, $character1, $character2){

        //if subtype was set it should be returned - unless the chosen species does not match it.
        $guaranteedSubtype = (isset($tag->getData()["subtype_id"])) ? Subtype::where('id', (int)$tag->getData()["subtype_id"])->first() : null;
        if($guaranteedSubtype != null && $speciesId == $guaranteedSubtype->species_id) return $guaranteedSubtype->id;


        $illegalSubtypes = (isset($tag->getData()["illegal_subtype_id"])) ? $tag->getData()["illegal_subtype_id"] : null;
        $defaultSubtypeId = (isset($tag->getData()["default_subtype_id"])) ? (int)$tag->getData()["default_subtype_id"] : null;
        $sub1 = $character1->image->subtype;
        $sub2 = $character2->image->subtype;

        //get only valid subtypes
        $validSubtypes = [];
        if($illegalSubtypes == null) {
            if($sub1 != null) $validSubtypes[] = $sub1;
            if($sub2 != null) $validSubtypes[] = $sub2;
        } else {
            if($sub1 != null && !in_array($sub1->id, $illegalSubtypes)) $validSubtypes[] = $sub1;
            if($sub2 != null && !in_array($sub2->id, $illegalSubtypes)) $validSubtypes[] = $sub2;
        }

        //in case a species was set for inheritance, or via default species, set subtype as null/open for choice.
        if($speciesId != $species1Id && $speciesId != $species2Id) {
            return null;
        }

        if($species1Id == $species2Id){
            if(count($validSubtypes) == 1){
                //1 valid subtype always wins
                $subtypeId = $validSubtypes[0]->id;
            }elseif(count($validSubtypes) > 1){
                //more than one valid subtype go by inherit chance
                $subtypeId = $validSubtypes[array_rand($validSubtypes)];
                $inheritSub1 = (random_int(0, $sub1->inherit_chance + $sub2->inherit_chance) <= $sub1->inherit_chance) ? true : false;
                if($inheritSub1) return $sub1->id;
                return $sub2->id;

            } else {
                $subtypeId = ($defaultSubtypeId != null) ? $defaultSubtypeId : null;
            }
        } else {
            //different specieses - subtype is the type of the parent whose species was chosen
            if($species1Id == $speciesId && in_array($sub1, $validSubtypes)){
                $subtypeId = $sub1->id;
            } elseif($species2Id == $speciesId && in_array($sub2, $validSubtypes)){
                $subtypeId = $sub2->id;
            } else {
                //if default is set go default
                $subtypeId = $defaultSubtypeId;
            }
        }
        return $subtypeId;
    }


    private function getRarityId($character1, $character2, $chosenFeatures){
        $rarity_sorts = [];
        //add all chosen features to rarity ids
        foreach($chosenFeatures as $feature) {
            $rarity = $feature->rarity;
            $rarity_sorts[] = $rarity->sort;
        }

        //WARNING this assumes the highest rarity has the highest sort number and sort 0 is the lowest
        $rarity_sort = count($rarity_sorts) > 0 ? max($rarity_sorts) : 0;
        return Rarity::where("sort", $rarity_sort)->first()->id;
    }

    
    private function getChosenFeatures($tag, $character1, $character2, $featurePool, $boosts){

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
        $featuresByCategory = $featurePool->groupBy('feature_category_id');
        $chosenFeatures = [];
        foreach($featuresByCategory as $categoryId=>$features){
            $features = $features->shuffle();
            $category = FeatureCategory::where('id', $categoryId)->first();
            //if no category is set, make min inheritable 0 and max inheritable 100 (basically, unlimited)
            $minInheritable = (isset($category)) ? $category->min_inheritable : 0;
            $maxInheritable = (isset($category)) ? $category->max_inheritable : 100;

            $featuresCalculated = 0;
            $featuresChosen = 0;
            $i = 0;
            //loop over features until min amount is chosen but never more than max amount
            while(($featuresCalculated < count($features) && $featuresChosen < $maxInheritable) || ($featuresChosen < $minInheritable)){
                $feature = $features[$i];
                $inheritChance = (isset($boostedChanceByRarity[$feature->rarity->id])) ? $boostedChanceByRarity[$feature->rarity->id] : $feature->rarity->inherit_chance;
                //calc inheritance chance
                $doesGetThisFeature = (random_int(0,100) <= $inheritChance) ? true : false;                    
                if($doesGetThisFeature) {
                    $chosenFeatures[$feature->id] = $feature;
                    $featuresChosen += 1;
                }
                $featuresCalculated += 1;
                $i = ($i === count($features) - 1) ? 0 : $i += 1;
            }
        }

        //set pairing feature if parents are of different subtypes or species
        if(isset($tag->getData()["feature_id"]) && ($character1->image->subtype_id != $character2->image->subtype_id || $character1->image->species_id != $character2->image->species_id)){
            $pairingFeature = Feature::where("id", $tag->getData()["feature_id"])->first();
            $chosenFeatures[$pairingFeature->id] = $pairingFeature;
        }

        return $chosenFeatures;

    }


    private function getSpeciesId($tag, $species1, $species2){

        // if a species was set for the item, it should be that one.
        if(isset($tag->getData()['species_id'])) return $tag->getData()['species_id'];
       
        $illegalSpecieses = (isset($tag->getData()["illegal_species_id"])) ? $tag->getData()["illegal_species_id"] : null;
        $defaultSpeciesId = (isset($tag->getData()["default_species_id"])) ? (int)$tag->getData()["default_species_id"] : null;

        $validSpecies = [];
        if($illegalSpecieses == null) {
            $validSpecies = [$species1, $species2];
        } else {
            if(!in_array($species1->id, $illegalSpecieses)) $validSpecies[] = $species1;
            if(!in_array($species2->id, $illegalSpecieses)) $validSpecies[] = $species2;
        }

        if(count($validSpecies) == 1){
            // Only one valid species means it gets picked regardless of inherit chance
            return $validSpecies[0]->id;
        } elseif(count($validSpecies) > 1){
            // chance of inheriting either species when both are valid
            $inheritSpecies1 = (random_int(0, $species1->inherit_chance + $species2->inherit_chance) <= $species1->inherit_chance) ? true : false;
            if($inheritSpecies1) return $species1->id;
            return $species2->id;
        } else {
            return $defaultSpeciesId; //should never be null as pairing gets rejected when no default is set and no species is valid
        }

    }

    private function saveMyo($user, $sex, $speciesId, $subtypeId, $rarityId, $feature_ids, $featureData, $character1Slug, $character2Slug){
        //set user who the slot belongs to
        $characterData['user_id'] = $user->id;
        //other vital data that is default
        $characterData['name'] = "Pairing Slot";
        $characterData['transferrable_at'] = null;
        $characterData['is_myo_slot'] = 1;
        $characterData['description'] = "A MYO slot created from a Pairing of: ".$character1Slug." and ".$character2Slug.".";

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
        $characterData['is_visible'] = true;
        $characterData['sale_value'] = 0;

        //species info
        $characterData['sex'] = $sex;
        $characterData['species_id'] = $speciesId;
        $characterData['subtype_id'] = isset($subtypeId) && $subtypeId ? $subtypeId : null;

        $characterData['feature_id'] = $feature_ids;
        $characterData['feature_data'] = $featureData;
        $characterData['rarity_id'] = $rarityId;

        // create slot
        $charService = new CharacterManager;
        $character = $charService->createCharacter($characterData, $user, true);
        return $character;
    }
}