<?php

namespace App\Services;

use App\Models\Character\Character;
use App\Models\Feature\Feature;
use App\Models\Item\Item;
use App\Models\Notification;
use App\Models\Pairing\Pairing;
use App\Models\Rarity;
use App\Models\Species\Species;
use App\Models\Species\Subtype;
use App\Models\User\User;
use App\Models\User\UserItem;
use Carbon\Carbon;
use Config;
use DB;
use Illuminate\Support\Arr;
use Notifications;
use Log;

class PairingManager extends Service {
    /*
    |--------------------------------------------------------------------------
    | Pairing Manager
    |--------------------------------------------------------------------------
    |
    | Handles creation and modification of pairing data.
    |
    */

    // constructor
    public function __construct() {
        parent::__construct();
        $this->keys = array_keys(Config::get('lorekeeper.character_pairing'));
    }

    /**********************************************************************************************

        PAIRING CREATION

    **********************************************************************************************/

    /**
     * Creates a new pairing.
     *
     * @param mixed $user
     * @param mixed $data
     *
     * @return \App\Models\Pairing\Pairing|bool
     */
    public function createPairing($data, $user) {
        DB::beginTransaction();

        try {
            //check that an item is attached
            if (!isset($data['stack_id'])) {
                throw new \Exception('Please attach a pairing item.');
            }

            $user_items = UserItem::whereIn('id', $data['stack_id'])->where('count', '>', 0)->get();
            $pairing_item = Item::whereRelation('tags', 'tag', 'pairing')->whereIn('id', $user_items->pluck('item_id'))->get();
            //check that exactly one valid pairing item is attached
            if ($pairing_item->count() != 1) {
                throw new \Exception('Pairing item not set correctly. Make sure to pick exactly one pairing item.');
            }

            $character_1 = Character::where('slug', $data['character_codes'][0])->first();
            $character_2 = Character::where('slug', $data['character_codes'][1])->first();

            //check cooldown if set to do so.
            $cooldown_days = Config::get('lorekeeper.character_pairing.cooldown');
            if ($cooldown_days) {
                if (Pairing::whereIn('status', ['IN PROGRESS'])->where('created_at', '>', Carbon::now()->subDays($cooldown_days))
                    // check if either character is in a pairing
                    ->where(function ($query) use ($character_1, $character_2) {
                        $query->where('character_1_id', $character_1->id)
                            ->orWhere('character_2_id', $character_1->id)
                            // chara 2
                            ->orWhere('character_1_id', $character_2->id)
                            ->orWhere('character_2_id', $character_2->id);
                    })->exists()) {
                    throw new \Exception('One of the characters selected is currently on pairing cooldown.');
                }
            }

            //do further checks
            if (!$this->validatePairingBasics($data['character_codes'], $pairing_item->first())) {
                throw new \Exception('Pairing failed validation.');
            }

            // set all assets
            $seen_items = [];
            if (isset($data['stack_id'])) {
                $user_assets = createAssetsArray();

                foreach ($data['stack_id'] as $id) {
                    $stack = UserItem::find($id);
                    if (!$stack || $stack->user_id != $user->id) {
                        throw new \Exception('Invalid item selected.');
                    }
                    if (!isset($data['stack_quantity'][$id]) || $data['stack_quantity'][$id] > 1) {
                        throw new \Exception('Invalid quantity selected.');
                    }
                    // make sure the item has either a boost tag or pairing tag
                    if (!$stack->item->tag('boost') && !$stack->item->tag('pairing')) {
                        throw new \Exception('Invalid item selected.');
                    }
                    // make sure the item is not already in the list
                    // this is to prevent boost stacking
                    if (in_array($stack->item->id, $seen_items)) {
                        throw new \Exception('Invalid item selected.');
                    }
                    $seen_items[] = $stack->item->id;
                    $stack->pairing_count += $data['stack_quantity'][$id];
                    $stack->save();
                    addAsset($user_assets, $stack, $data['stack_quantity'][$id]);
                }
            }

            //create pairing
            $pairing = Pairing::create([
                'user_id'              => $user->id,
                'character_1_id'       => $character_1->id,
                'character_2_id'       => $character_2->id,
                'character_1_approved' => $character_1->user_id == $user->id,
                'character_2_approved' => $character_2->user_id == $user->id,
                'data'                 => json_encode(['user' => Arr::only(getDataReadyAssets($user_assets), ['user_items'])]),
            ]);

            if (!$pairing) {
                throw new \Exception('Error happened while trying to create pairing.');
            }

            // if both characters are owned by the user, set status to 'APPROVED'
            if ($pairing->character_1_approved && $pairing->character_2_approved) {
                $pairing->status = 'APPROVED';
                $pairing->save();
            }

            // notify other users if approval is needed
            if ($character_1->user_id != $user->id) {
                Notifications::create('PAIRING_NEW_APPROVAL', $character_1->user, [
                    'character_1_url'  => $character_1->url,
                    'character_1_slug' => $character_1->slug,
                    'character_2_url'  => $character_2->url,
                    'character_2_slug' => $character_2->slug,
                ]);
            }
            // only send one notif if the 2 chars belong to the same person
            if ($character_1->user_id != $character_2->user_id && $character_2->user_id != $user->id) {
                Notifications::create('PAIRING_NEW_APPROVAL', $character_2->user, [
                    'character_1_url'  => $character_1->url,
                    'character_1_slug' => $character_1->slug,
                    'character_2_url'  => $character_2->url,
                    'character_2_slug' => $character_2->slug,
                ]);
            }

            return $this->commitReturn($pairing);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }

    /**
     * Validates that all information is correct for a pairing.
     *
     * @param array $character_codes
     * @param Item  $pairing_item
     *
     * @return bool
     */
    public function validatePairingBasics($character_codes, $pairing_item) {
        try {
            if (!isset($character_codes) || count($character_codes) != 2) {
                throw new \Exception('Please enter two character codes.');
            }
            if ($character_codes[0] == $character_codes[1]) {
                throw new \Exception('Pairings must be between two different characters.');
            }

            $character_1 = Character::where('slug', $character_codes[0])->first();
            $character_2 = Character::where('slug', $character_codes[1])->first();

            if (!isset($character_1) || !isset($character_2)) {
                throw new \Exception('Invalid Character(s) set.');
            }

            // set and check tag
            $tag = $pairing_item->tag('pairing');
            if (!$tag) {
                throw new \Exception('Item is missing the required pairing tag.');
            }

            //check sex if set to do so. If one char has no sex it always works.
            if (Config::get('lorekeeper.character_pairing.sex_restriction')) {
                if (isset($character_1->image->sex) && isset($character_2->image->sex)) {
                    if ($character_1->image->sex == $character_2->image->sex) {
                        throw new \Exception('Pairings can only be created between characters of differing sex.');
                    }
                }
            }

            $species_1 = $character_1->image->species;
            $species_2 = $character_2->image->species;

            //check if the pairing type matches the character input
            //pairing type 0 = species, 1 = subtype
            $pairing_type = $tag->getData()['pairing_type'] ?? null;
            if (isset($pairing_type)) {
                if ($pairing_type && $species_1->id != $species_2->id) {
                    throw new \Exception('A subtype pairing can only be done with characters of the same species.');
                }
                if (!$pairing_type && $species_1->id == $species_2->id) {
                    throw new \Exception('A species pairing can only be done with characters of the same species.');
                }
            }

            // check if correct species was used for the characters
            $illegal_species = (isset($tag->getData()['illegal_species_id'])) ? $tag->getData()['illegal_species_id'] : null;
            $valid_species_ids = array_diff([$species_1->id, $species_2->id], $illegal_species ?? []);

            if (count($valid_species_ids) < 1 && !isset($tag->getData()['default_species_id'])) {
                throw new \Exception('This item cannot create a pairing from the specieses of the chosen characters.');
            }

            // check if correct subtypes were used for the characters
            $illegal_subtypes = (isset($tag->getData()['illegal_subtype_id'])) ? $tag->getData()['illegal_subtype_id'] : null;
            $valid_subtype_ids = array_diff([$character_1->image->subtype?->id, $character_2->image->subtype?->id], $illegal_subtypes ?? []);
            if (count($valid_subtype_ids) <= 0 && !isset($tag->getData()['default_subtype_id'])) {
                throw new \Exception('This item cannot create a pairing from the subtypes of the chosen characters.');
            }

            return true;
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }
    }

    /**
     * Cancels a pairing.
     *
     * @param mixed $pairing
     */
    public function cancelPairing($pairing) {
        DB::beginTransaction();

        try {
            if (!$pairing) {
                throw new \Exception('Error happened while trying to approve pairing.');
            }

            $pairing->status = 'CANCELLED';
            $pairing->save();

            // Return all added items
            $addon_data = $pairing->data['user'];
            if (isset($addon_data['user_items'])) {
                foreach ($addon_data['user_items'] as $id => $quantity) {
                    $user_item = UserItem::find($id);
                    if (!$user_item) {
                        throw new \Exception('Cannot return an invalid item. (#'.$id.')');
                    }
                    if ($user_item->pairing_count < $quantity) {
                        throw new \Exception('Cannot return more items than was held. (#'.$id.')');
                    }
                    $user_item->pairing_count -= $quantity;
                    $user_item->save();
                }
            }

            // if one of the characters is not owned by the user notify the other party
            // of cancellation
            if ($pairing->character_1->user_id != $pairing->user_id) {
                Notifications::create('PAIRING_CANCELLED', $pairing->character_1->user, [
                    'character_1_url'  => $pairing->character_1->url,
                    'character_1_slug' => $pairing->character_1->slug,
                    'character_2_url'  => $pairing->character_2->url,
                    'character_2_slug' => $pairing->character_2->slug,
                ]);
            }
            if ($pairing->character_2->user_id != $pairing->user_id) {
                Notification::create('PAIRING_CANCELLED', $pairing->character_2->user, [
                    'character_1_url'  => $pairing->character_1->url,
                    'character_1_slug' => $pairing->character_1->slug,
                    'character_2_url'  => $pairing->character_2->url,
                    'character_2_slug' => $pairing->character_2->slug,
                ]);
            }

            return $this->commitReturn(true);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }

    /**
     * Approve a pairing.
     *
     * @param mixed $pairing
     *
     * @return \App\Models\Pairing\Pairing|bool
     */
    public function approvePairing($pairing) {
        DB::beginTransaction();

        try {
            if (!$pairing) {
                throw new \Exception('Error happened while trying to approve pairing.');
            }

            //set approval
            $pairing->character_1_approved = 1;
            $pairing->character_2_approved = 1;
            $pairing->status = 'APPROVED';
            $pairing->save();

            // Notify the user
            Notifications::create('PAIRING_APPROVED', $pairing->user, [
                'character_1_url'  => $pairing->character_1->url,
                'character_1_slug' => $pairing->character_1->slug,
                'character_2_url'  => $pairing->character_2->url,
                'character_2_slug' => $pairing->character_2->slug,
            ]);

            return $this->commitReturn($pairing);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }

    /**
     * Reject a pairing.
     *
     * @param mixed $user
     * @param mixed $pairing
     *
     * @return \App\Models\Pairing\Pairing|bool
     */
    public function rejectPairing($pairing, $user) {
        DB::beginTransaction();

        try {
            if (!$pairing) {
                throw new \Exception('Error happened while trying to reject pairing.');
            }

            $pairing->status = 'REJECTED';
            $pairing->save();

            // Return all added items
            $addon_data = $pairing->data['user'];
            if (isset($addon_data['user_items'])) {
                foreach ($addon_data['user_items'] as $id => $quantity) {
                    $user_item = UserItem::find($id);
                    if (!$user_item) {
                        throw new \Exception('Cannot return an invalid item. (#'.$id.')');
                    }
                    if ($user_item->pairing_count < $quantity) {
                        throw new \Exception('Cannot return more items than was held. (#'.$id.')');
                    }
                    $user_item->pairing_count -= $quantity;
                    $user_item->save();
                }
            }

            // Notify the user
            if ($pairing->user->id != $user->id) {
                Notifications::create('PAIRING_REJECTED', $pairing->user, [
                    'character_1_url'  => $pairing->character_1->url,
                    'character_1_slug' => $pairing->character_1->slug,
                    'character_2_url'  => $pairing->character_2->url,
                    'character_2_slug' => $pairing->character_2->slug,
                ]);
            }

            return $this->commitReturn($pairing);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }

    /**
     * Create MYO.
     *
     * @param mixed $user
     * @param mixed $pairing
     *
     * @return \App\Models\Pairing\Pairing|bool
     */
    public function createMyos($pairing, $user) {
        DB::beginTransaction();

        try {
            if (!$pairing) {
                throw new \Exception('Error happened while trying to create a MYO from the pairing.');
            }

            if ($pairing->status != 'APPROVED') {
                throw new \Exception('Pairing is not ready to complete yet.');
            }

            $pairing_item = null;
            $boosts = [];

            $addon_data = $pairing->data['user'];
            if (isset($addon_data['user_items'])) {
                foreach ($addon_data['user_items'] as $id => $quantity) {
                    $user_item = UserItem::find($id);
                    if (!$user_item) {
                        throw new \Exception('Cannot use an invalid item. (#'.$id.')');
                    }
                    if ($user_item->item->tag('pairing')) {
                        $pairing_item = $user_item->item;
                    }
                    if ($user_item->item->tag('boost')) {
                        $boosts[] = $user_item->item;
                    }
                }
            }

            if (!$pairing_item) {
                throw new \Exception('Pairing item not set correctly.');
            }
            $tag = $pairing_item->tag('pairing');

            // Remove any added items, hold counts, and add logs
            $inventoryManager = new InventoryManager;
            if (isset($pairing->data['user']['user_items'])) {
                $stacks = $pairing->data['user']['user_items'];
                foreach ($stacks as $id => $quantity) {
                    $user_item = UserItem::find($id);
                    if (!$user_item) {
                        throw new \Exception('Cannot return an invalid item. (#'.$id.')');
                    }
                    if ($user_item->pairing_count < $quantity) {
                        throw new \Exception('Cannot return more items than was held. (#'.$id.')');
                    }
                    $user_item->pairing_count -= $quantity;
                    $user_item->save();

                    if (!$inventoryManager->debitStack(
                        $user,
                        'Used in Pairing',
                        ['data' => 'Item used in pairing between '.$pairing->character_1->displayName.' and '.$pairing->character_2->displayName],
                        $user_item,
                        $quantity
                    )) {
                        throw new \Exception('Failed to create log for item stack.');
                    }
                }
            }

            $characters = [$pairing->character_1, $pairing->character_2];
            $species = [$pairing->character_1->image->species, $pairing->character_2->image->species];
            $myoAmount = random_int($tag->getData()['min'], $tag->getData()['max']);

            //loop over for each myo
            for ($i = 0; $i < $myoAmount; $i++) {
                $sex = $this->getSex($boosts);
                $inherit_traits_from_both = $this->getInheritTraitsFromBoth($boosts);
                $species_id = $this->getSpeciesId($tag, $species, $inherit_traits_from_both);
                $subtype_id = $this->getSubtypeId($tag, $species, $characters, $species_id);
                //
                $feature_pool = $this->getFeaturePool($tag, $characters, $boosts, $species_id, $subtype_id, $inherit_traits_from_both);
                $chosen_features_ids = $this->getChosenFeatures($tag, $characters, $feature_pool, $boosts);
                $feature_data = $this->getFeatureData($tag, $characters, $species, $chosen_features_ids, $species_id, $subtype_id);
                $rarity_id = $this->getRarityId($boosts, $chosen_features_ids);

                //create MYO
                $myo = $this->saveMyo(
                    $user,
                    $sex,
                    $species_id,
                    $subtype_id,
                    $rarity_id,
                    array_unique(array_keys($chosen_features_ids)),
                    $feature_data,
                    $characters
                );

                if (!$myo) {
                    throw new \Exception('Could not create MYO slot.');
                }
            }
            //update status
            $pairing->status = 'COMPLETE';
            $pairing->save();

            return $this->commitReturn($myoAmount);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }

    /**********************************************************************************************

        TEST ROLLS

    **********************************************************************************************/

    /**
     * Rolls Test MYOs without saving them.
     *
     * @param mixed $data
     * @param mixed $user
     *
     * @return \App\Models\Pairing\Pairing|bool
     */
    public function rollTestMyos($data, $user) {
        try {
            $items = Item::whereRelation('tags', 'tag', 'pairing')->where('id', $data['pairing_item_id'])->get();
            //check that exactly one valid pairing item is attached
            if ($items->count() != 1) {
                throw new \Exception('Pairing item not set correctly. Make sure to pick exactly one pairing item.');
            }

            $data['boost_item_ids'] = array_filter($data['boost_item_ids']);
            $boosts = Item::whereRelation('tags', 'tag', 'boost')->whereIn('id', $data['boost_item_ids'])->get();
            // make sure there are no duplicate boosts (check boost_item_ids)
            if (count($boosts) != count($data['boost_item_ids'])) {
                throw new \Exception('Invalid or multiple of the same boost item selected.');
            }

            $test_myos = [];
            if ($this->validatePairingBasics($data['character_codes'], $items->first())) {
                $character_1 = Character::where('slug', $data['character_codes'][0])->first();
                $character_2 = Character::where('slug', $data['character_codes'][1])->first();

                if (!$character_1 || !$character_2) {
                    throw new \Exception('Invalid Character set.');
                }
                $tag = $items->first()->tag('pairing');
                if (!$tag) {
                    throw new \Exception('Item is missing the required pairing tag.');
                }

                $characters = [$character_1, $character_2];
                $species = [$character_1->image->species, $character_2->image->species];
                $myo_count = random_int($tag->getData()['min'], $tag->getData()['max']);

                //loop over for each myo
                for ($i = 0; $i < $myo_count; $i++) {
                    $sex = $this->getSex($boosts);
                    $inherit_traits_from_both = $this->getInheritTraitsFromBoth($boosts);
                    $species_id = $this->getSpeciesId($tag, $species, $inherit_traits_from_both);
                    $subtype_id = $this->getSubtypeId($tag, $species, $characters, $species_id);
                    //
                    $feature_pool = $this->getFeaturePool($tag, $characters, $boosts, $species_id, $subtype_id, $inherit_traits_from_both);
                    $chosen_features_ids = $this->getChosenFeatures($tag, $characters, $feature_pool, $boosts);
                    $feature_data = $this->getFeatureData($tag, $characters, $species, $chosen_features_ids, $species_id, $subtype_id);
                    $rarity_id = $this->getRarityId($boosts, $chosen_features_ids);
                    $test_myos[] = [
                        'user'         => $user,
                        'sex'          => $sex,
                        'species'      => Species::where('id', $species_id)->first()->displayName,
                        'subtype'      => Subtype::where('id', $subtype_id)->first()?->displayName,
                        'rarity'       => Rarity::where('id', $rarity_id)->first()->displayName,
                        'features'     => $chosen_features_ids,
                        'feature_data' => $feature_data,
                    ];
                }
            }

            return $test_myos;
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }
    }

    /**********************************************************************************************

        MYO CREATION FUNCTIONS

    **********************************************************************************************/

    /**
     * Determines the sex of the MYO.
     *
     * @param mixed $boosts
     *
     * @return string|null
     */
    private function getSex($boosts) {
        $male_percentage = Config::get('lorekeeper.character_pairing.offspring_male_percentage');
        $female_percentage = Config::get('lorekeeper.character_pairing.offspring_female_percentage');
        foreach ($boosts as $boost) {
            if ($boost->tag('boost') && isset($boost->tag('boost')->getData()['setting'])) {
                if ($this->keys[$boost->tag('boost')->getData()['setting']] == 'offspring_male_percentage') {
                    $male_boost = $boost->tag('boost')->getData()['setting_chance'];
                }
                if ($this->keys[$boost->tag('boost')->getData()['setting']] == 'offspring_female_percentage') {
                    $female_boost = $boost->tag('boost')->getData()['setting_chance'];
                }
            }
        }

        //sex is disabled in site settings
        if (!$male_percentage && !$female_percentage) {
            return null;
        }

        //prioritize boosts
        // can only have one boost of either type
        if (isset($male_boost)) {
            return (random_int(0, 100) <= $male_boost) ? 'Male' : 'Female';
        }
        if (isset($female_boost)) {
            return (random_int(0, 100) <= $female_boost) ? 'Female' : 'Male';
        }

        //otherwise use settings
        if ($male_percentage + $female_percentage == 100) {
            return (random_int(0, 100) <= $female_percentage) ? 'Female' : 'Male';
        } else {
            throw new \Exception('Male and female chance is not set to a total of 100. Please contact a mod/admin.');
        }
    }

    /**
     * Determines how likely it is that the MYO inherits traits from both parents.
     *
     * @param mixed $boosts
     *
     * @return bool
     */
    private function getInheritTraitsFromBoth($boosts) {
        // TODO

        foreach ($boosts as $boost) {
            if ($boost->tag('boost') && isset($boost->tag('boost')->getData()['setting'])) {
                if ($boost->tag('boost')->getData()['setting'] == 'pairing_trait_inheritance') {
                    $inherit_boost = $boost->tag('boost')->getData()['setting_chance'];
                }
            }
        }

        $inherit_chance = (isset($inherit_boost)) ? $inherit_boost : Config::get('lorekeeper.character_pairing.trait_inheritance');

        return random_int(0, 100) <= $inherit_chance;
    }

    /**
     * Get the pool of features that could appear on this myo.
     *
     * @param mixed $tag
     * @param mixed $characters
     * @param mixed $boosts
     * @param mixed $species_id
     * @param mixed $subtype_id
     * @param mixed $inherit_traits_from_both
     *
     * @return \Illuminate\Support\Collection
     */
    private function getFeaturePool($tag, $characters, $boosts, $species_id, $subtype_id, $inherit_traits_from_both) {
        if ($inherit_traits_from_both) {
            //inherit traits from both parents
            $feature_ids = array_merge($characters[0]->image->features()->pluck('feature_id')->toArray(), $characters[1]->image->features()->pluck('feature_id')->toArray());
        } else {
            // randomly pick 1 parent to inherit traits from
            $feature_ids = $characters[random_int(0, 1)]->image->features()->pluck('feature_id')->toArray();
        }

        // filter features that are valid for the chosen species and subtype
        $features = Feature::where(function ($query) use ($species_id) {
            $query->where('species_id', $species_id)
                ->orWhere('species_id', null);
        })->where(function ($query) use ($subtype_id) {
            $query->where('subtype_id', $subtype_id)
                ->orWhere('subtype_id', null);
        })->get();

        // if illegal features were set, do not include those.
        $illegal_feature_ids = $tag->getData()['illegal_feature_ids'] ?? null;
        if ($illegal_feature_ids) {
            $features = $features->whereIn('id', $feature_ids)->whereNotIn('id', $illegal_feature_ids);
        } else {
            $features = $features->whereIn('id', $feature_ids);
        }

        return $features;
    }

    /**
     * Determines the features chosen for the MYO.
     *
     * @param mixed $tag
     * @param mixed $characters
     * @param mixed $feature_pool
     * @param mixed $boosts
     *
     * @return array
     */
    private function getChosenFeatures($tag, $characters, $feature_pool, $boosts) {
        $boosted_chance_by_rarity = [];
        // sort boosts by rarityid for easier access
        foreach ($boosts as $boost) {
            if ($boost->tag('boost') != null) {
                $boost_tag = $boost->tag('boost');
                if (isset($boost_tag->getData()['rarity_id'])) {
                    $boosted_chance_by_rarity[$boost_tag->getData()['rarity_id']] =
                        (isset($boosted_chance_by_rarity[$boost_tag->getData()['rarity_id']])) ?
                        // if it is set set the higher one to val
                        max($boosted_chance_by_rarity[$boost_tag->getData()['rarity_id']], $boost_tag->getData()['rarity_chance']) :
                        // if it is not set set it to val
                        $boost_tag->getData()['rarity_chance'];
                }
            }
        }

        //sort features by category
        $features_by_category = $feature_pool->groupBy('feature_category_id');
        $chosen_features = [];
        foreach ($features_by_category as $categoryId=>$features) {
            //if no category is set, make min inheritable 0 and max inheritable 100 (basically, unlimited)
            $min_inheritable = $features->first()->category->min_inheritable ?? 0;
            $max_inheritable = $features->first()->category->max_inheritable ?? 100;

            $features_calculated = 0;
            $features_chosen = 0;
            $i = 0;
            // shuffle features to randomize order so that the first features are not always chosen
            $features->shuffle();
            //loop over features until min amount is chosen but never more than max amount
            while (($features_calculated < count($features) && $features_chosen < $max_inheritable) || ($features_chosen < $min_inheritable)) {
                $feature = $features[$i];
                $inherit_chance = $boosted_chance_by_rarity[$feature->rarity->id] ?? $feature->rarity->inherit_chance;
                //calc inheritance chance
                $does_get_this_feature = (random_int(0, 100) <= $inherit_chance);
                if ($does_get_this_feature) {
                    $chosen_features[$feature->id] = $feature;
                    $features_chosen += 1;
                }
                $features_calculated += 1;
                // shuffle features to randomize order...
                $features->shuffle();
                $i = ($i == count($features) - 1) ? 0 : $i += 1;
            }
        }

        //set pairing feature if parents are of different subtypes or species
        if (isset($tag->getData()['feature_id']) &&
            ($characters[0]->image->subtype_id != $characters[1]->image->subtype_id || $characters[0]->image->species_id != $characters[1]->image->species_id)) {
            $pairing_feature = Feature::where('id', $tag->getData()['feature_id'])->first();
            $chosen_features[$pairing_feature->id] = $pairing_feature;
        }

        return $chosen_features;
    }

    /**
     * Gives feature data for traits granted by the tag so that it can be identified from which parent is was inherited.
     *
     * @param mixed $tag
     * @param mixed $characters
     * @param mixed $species
     * @param mixed $chosen_features
     * @param mixed $species_id
     * @param mixed $subtype_id
     *
     * @return array
     */
    private function getFeatureData($tag, $characters, $species, $chosen_features, $species_id, $subtype_id) {
        $pairing_feature_data = null;
        if ($species[0]->id == $species[1]->id) {
            //parents with the same species - subtype is 50:50 between parents
            if ($characters[0]->image->subtype_id && $characters[0]->image->subtype_id != $subtype_id) {
                $pairing_feature_data = $characters[0]->image->subtype?->name;
            }
            if ($characters[1]->image->subtype_id && $characters[1]->image->subtype_id != $subtype_id) {
                $pairing_feature_data = $characters[1]->image->subtype?->name;
            }
        } else {
            //subtype is the type of the parent whose species was chosen
            if ($characters[0]->image->species->id == $species_id) {
                $pairing_feature_data = $characters[1]->image->species->name;
            } elseif ($characters[1]->image->species->id == $species_id) {
                $pairing_feature_data = $characters[1]->image->species->name;
            } else {
                //if a species was chosen in the item tag, set feature data to nothing.
                $pairing_feature_data = null;
            }
        }

        $feature_data = [];
        // add all chosen features data as empty
        foreach ($chosen_features as $id=>$feature) {
            if (isset($tag->getData()['feature_id']) && $id == $tag->getData()['feature_id']) {
                // set subtype/species of other parent if a trait was set for it.
                $feature_data[] = $pairing_feature_data;
            }
        }

        return $feature_data;
    }

    /**
     * Determines the species of the MYO.
     *
     * @param mixed $tag
     * @param mixed $species
     * @param mixed $inherit
     *
     * @return int
     */
    private function getSpeciesId($tag, $species, $inherit) {
        // if a species was set for the item, and the result is a crossbreed with traits from both parents, set the crossbreed species.
        // if ($inherit && isset($tag->getData()['species_id'])) {
        if (isset($tag->getData()['species_id'])) {
            return $tag->getData()['species_id'];
        }

        $illegal_species_ids = $tag->getData()['illegal_species_ids'] ?? null;
        $default_species_id = $tag->getData()['default_species_id'] ?? null;
        $valid_species_ids = array_diff([$species[0]->id, $species[1]->id], $illegal_species_ids ?? []);

        if (count($valid_species_ids) > 1) {
            // chance of inheriting either species when both are valid
            $inherit_species = (random_int(0, $species[0]->inherit_chance + $species[1]->inherit_chance) <= $species[0]->inherit_chance) ? true : false;
            if ($inherit_species) {
                return $species[0]->id;
            }

            return $species[1]->id;
        } elseif (count($valid_species_ids) == 1) {
            return $valid_species_ids[0];
        } else {
            return $default_species_id; //should never be null as pairing gets rejected when no default is set and no species is valid
        }
    }

    /**
     * Determines the subtype id.
     *
     * @param mixed $tag
     * @param mixed $species
     * @param mixed $characters
     * @param mixed $species_id
     *
     * @return int
     */
    private function getSubtypeId($tag, $species, $characters, $species_id) {
        //if subtype was set it should be returned - unless the chosen species does not match it.

        $guaranteed_subtype = (isset($tag->getData()['subtype_id'])) ? Subtype::find($tag->getData()['subtype_id']) : null;
        if ($guaranteed_subtype && $guaranteed_subtype->species_id == $species_id) {
            return $guaranteed_subtype->id;
        }

        //in case a species was set for inheritance, or via default species, set subtype as null/open for choice.
        if ($species_id != $species[0]->id && $species_id != $species[1]->id) {
            return null;
        }

        $illegal_subtype_id = $tag->getData()['illegal_subtype_id'] ?? null;
        $default_subtype_id = $tag->getData()['default_subtype_id'] ?? null;
        $valid_subtypes = array_filter([$characters[0]->image->subtype, $characters[1]->image->subtype]);
        $valid_subtype_ids = array_filter(array_diff([$characters[0]->image->subtype?->id, $characters[1]->image->subtype?->id], $illegal_subtype_id ?? []));

        if ($species[0]->id == $species[1]->id) {
            if (count($valid_subtypes) > 1) {
                //more than one valid subtype go by inherit chance
                $inherit_chance = $characters[0]->image->subtype?->inherit_chance + $characters[1]->image->subtype?->inherit_chance;

                return (random_int(0, $inherit_chance) <= $characters[0]->image->subtype->inherit_chance) ? $valid_subtypes[0]->id : $valid_subtypes[1]->id;
            } elseif (count($valid_subtypes) == 1) {
                // check if no subtype or if subtype is valid
                // *2 to make it 50-50 chance
                return (random_int(0, array_values($valid_subtypes)[0]->inherit_chance * 2) <= array_values($valid_subtypes)[0]->inherit_chance)
                    ? array_values($valid_subtypes)[0]->id : $default_subtype_id;
            }
        } else {
            //different specieses - subtype is the type of the parent whose species was chosen
            return Subtype::whereIn('id', $valid_subtype_ids)->where('species_id', $species_id)->first()->id ?? $default_subtype_id;
        }

        // fallback
        return $default_subtype_id;
    }

    /**
     * Gives the character the rarity of the highest trait rarity.
     *
     * @param mixed $chosen_features
     */
    private function getRarityId($boosts, $chosen_features) {
        $features = Feature::whereIn('id', array_keys($chosen_features))->get();
        $rarity_sorts = $features->pluck('rarity.sort')->toArray();

        // WARNING this assumes the highest rarity has the highest sort number and sort 0 is the lowest
        $rarity_sort = count($rarity_sorts) > 0 ? max($rarity_sorts) : 0;

        // Log::info("rarity sort initial " . $rarity_sort);

        $boosted_rarities = collect();
        // get rarity boost
        foreach ($boosts as $boost) {
            $boost_tag = $boost->tag('boost');
            if (isset($boost_tag->getData()['rarity_id'])) {
                // get the rarity chance
                $chance = mt_rand(0, $boost_tag->getData()['rarity_chance']) <= $boost_tag->getData()['rarity_chance'];
                if ($chance) {
                    $boosted_rarities[] = Rarity::find($boost_tag->getData()['rarity_id']);
                }
            }
        }

        // if there are boosted rarities, get the highest one
        if (count($boosted_rarities)) {
            $boosted_rarity_sorts = $boosted_rarities->pluck('sort')->toArray();
            $rarity_sort = max([$rarity_sort, max($boosted_rarity_sorts)]);
        }

        // Log::info("rarity sort final " . $rarity_sort);

        return Rarity::where('sort', $rarity_sort)->first()->id;
    }

    /**
     * Creates the myo slot.
     *
     * @param mixed $user
     * @param mixed $sex
     * @param mixed $species_id
     * @param mixed $subtype_id
     * @param mixed $rarity_id
     * @param mixed $feature_ids
     * @param mixed $feature_data
     * @param mixed $characters
     */
    private function saveMyo($user, $sex, $species_id, $subtype_id, $rarity_id, $feature_ids, $feature_data, $characters) {
        DB::beginTransaction();

        try {
            //set user who the slot belongs to
            $characterData['user_id'] = $user->id;
            //other vital data that is default
            $characterData['name'] = 'Pairing Slot';
            $characterData['transferrable_at'] = null;
            $characterData['is_myo_slot'] = 1;
            $characterData['description'] = 'A MYO slot created from a pairing of: '.$characters[0]->displayName.' and '.$characters[1]->displayName.'.';

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
            $characterData['species_id'] = $species_id;
            $characterData['subtype_id'] = isset($subtype_id) && $subtype_id ? $subtype_id : null;

            $characterData['feature_id'] = Feature::whereIn('id', array_keys($feature_ids))->get();
            $characterData['feature_data'] = $feature_data;
            $characterData['rarity_id'] = $rarity_id;

            // create slot
            $charService = new CharacterManager;
            $character = $charService->createCharacter($characterData, $user, true);

            return $this->commitReturn($character);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }
}
