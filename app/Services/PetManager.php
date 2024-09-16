<?php

namespace App\Services;

use App\Models\Character\Character;
use App\Models\Pet\Pet;
use App\Models\Pet\PetDrop;
use App\Models\User\User;
use App\Models\User\UserItem;
use App\Models\User\UserPet;
use Auth;
use Carbon\Carbon;
use DB;
use Illuminate\Support\Arr;
use Notifications;

class PetManager extends Service {
    /*
    |--------------------------------------------------------------------------
    | Inventory Manager
    |--------------------------------------------------------------------------
    |
    | Handles modification of user-owned pets.
    |
    */

    /**
     * Grants an pet to multiple users.
     *
     * @param array $data
     * @param User  $staff
     *
     * @return bool
     */
    public function grantPets($data, $staff) {
        DB::beginTransaction();

        try {
            foreach ($data['quantities'] as $q) {
                if ($q <= 0) {
                    throw new \Exception('All quantities must be at least 1.');
                }
            }

            // Process names
            $users = User::find($data['names']);
            if (count($users) != count($data['names'])) {
                throw new \Exception('An invalid user was selected.');
            }

            $keyed_quantities = [];
            array_walk($data['pet_ids'], function ($id, $key) use (&$keyed_quantities, $data) {
                if ($id != null && !in_array($id, array_keys($keyed_quantities), true)) {
                    $keyed_quantities[$id] = $data['quantities'][$key];
                }
            });

            $keyed_variant = [];
            array_walk($data['pet_ids'], function ($id, $key) use (&$keyed_variant, $data) {
                if ($id != null && !in_array($id, array_keys($keyed_variant), true)) {
                    $keyed_variant[$id] = $data['variant'][$key];
                }
            });

            $keyed_evolution = [];
            array_walk($data['pet_ids'], function ($id, $key) use (&$keyed_evolution, $data) {
                if ($id != null && !in_array($id, array_keys($keyed_evolution), true)) {
                    $keyed_evolution[$id] = $data['evolution'][$key];
                }
            });

            // Process pet
            $pets = Pet::find($data['pet_ids']);
            if (!count($pets)) {
                throw new \Exception('No valid pets found.');
            }

            foreach ($users as $user) {
                foreach ($pets as $pet) {
                    if ($this->creditPet($staff, $user, 'Staff Grant', Arr::only($data, ['data', 'disallow_transfer', 'notes']), $pet, $keyed_quantities[$pet->id], $keyed_variant[$pet->id] ?? null, $keyed_evolution[$pet->id] ?? null)) {
                        Notifications::create('PET_GRANT', $user, [
                            'pet_name'     => $pet->name,
                            'pet_quantity' => $keyed_quantities[$pet->id],
                            'sender_url'   => $staff->url,
                            'sender_name'  => $staff->name,
                        ]);
                    } else {
                        throw new \Exception('Failed to credit pets to '.$user->name.'.');
                    }
                }
            }

            return $this->commitReturn(true);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }

    /**
     * Transfers an pet stack between users.
     *
     * @param User    $sender
     * @param User    $recipient
     * @param UserPet $stack
     *
     * @return bool
     */
    public function transferStack($sender, $recipient, $stack) {
        DB::beginTransaction();

        try {
            if (!$sender->hasAlias) {
                throw new \Exception('Your deviantART account must be verified before you can perform this action.');
            }
            if (!$stack) {
                throw new \Exception('Invalid pet selected.');
            }
            if ($stack->user_id != $sender->id && !$sender->hasPower('edit_inventories')) {
                throw new \Exception('You do not own this pet.');
            }
            if ($stack->user_id == $recipient->id) {
                throw new \Exception("Cannot send an pet to the pet's owner.");
            }
            if (!$recipient) {
                throw new \Exception('Invalid recipient selected.');
            }
            if (!$recipient->hasAlias) {
                throw new \Exception('Cannot transfer pets to a non-verified member.');
            }
            if ($recipient->is_banned) {
                throw new \Exception('Cannot transfer pets to a banned member.');
            }
            if ((!$stack->pet->allow_transfer || isset($stack->data['disallow_transfer'])) && !$sender->hasPower('edit_inventories')) {
                throw new \Exception('This pet cannot be transferred.');
            }

            $oldUser = $stack->user;
            if ($this->moveStack($stack->user, $recipient, ($stack->user_id == $sender->id ? 'User Transfer' : 'Staff Transfer'), ['data' => ($stack->user_id != $sender->id ? 'Transferred by '.$sender->displayName : '')], $stack)) {
                Notifications::create('PET_TRANSFER', $recipient, [
                    'pet_name'     => $stack->pet->name,
                    'pet_quantity' => 1,
                    'sender_url'   => $sender->url,
                    'sender_name'  => $sender->name,
                ]);
                if ($stack->user_id != $sender->id) {
                    Notifications::create('FORCED_PET_TRANSFER', $oldUser, [
                        'pet_name'     => $stack->pet->name,
                        'pet_quantity' => 1,
                        'sender_url'   => $sender->url,
                        'sender_name'  => $sender->name,
                    ]);
                }

                return $this->commitReturn(true);
            }
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }

    /**
     * Deletes an pet stack.
     *
     * @param User    $user
     * @param UserPet $stack
     *
     * @return bool
     */
    public function deleteStack($user, $stack) {
        DB::beginTransaction();

        try {
            if (!$user->hasAlias) {
                throw new \Exception('Your deviantART account must be verified before you can perform this action.');
            }
            if (!$stack) {
                throw new \Exception('Invalid pet selected.');
            }
            if ($stack->user_id != $user->id && !$user->hasPower('edit_inventories')) {
                throw new \Exception('You do not own this pet.');
            }

            $oldUser = $stack->user;

            if ($this->debitStack($stack->user, ($stack->user_id == $user->id ? 'User Deleted' : 'Staff Deleted'), ['data' => ($stack->user_id != $user->id ? 'Deleted by '.$user->displayName : '')], $stack)) {
                if ($stack->user_id != $user->id) {
                    Notifications::create('PET_REMOVAL', $oldUser, [
                        'pet_name'     => $stack->pet->name,
                        'pet_quantity' => 1,
                        'sender_url'   => $user->url,
                        'sender_name'  => $user->name,
                    ]);
                }

                return $this->commitReturn(true);
            }
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }

    /**
     * Names a pet stack.
     *
     * @param  UserPet
     * @param mixed $pet
     * @param mixed $name
     *
     * @return bool
     */
    public function nameStack($pet, $name) {
        DB::beginTransaction();

        try {
            $user = Auth::user();
            if (!$user->hasAlias) {
                throw new \Exception('Your deviantART account must be verified before you can perform this action.');
            }
            if (!$pet) {
                throw new \Exception('An invalid pet was selected.');
            }
            if ($pet->user_id != $user->id && !$user->hasPower('edit_inventories')) {
                throw new \Exception('You do not own this pet.');
            }

            $pet['pet_name'] = $name;
            $pet->save();

            return $this->commitReturn(true);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }

    /**
     * attaches a pet stack.
     *
     * @param mixed $pet
     * @param mixed $id
     *
     * @return bool
     */
    public function attachStack($pet, $id) {
        DB::beginTransaction();

        try {
            // First, check user permissions
            $user = Auth::user();

            // Next, why bother checking everything else if the pet isn't even attachable? Also determine if the user is the owner of the pet/has permission to attach.
            if (!$pet) {
                throw new \Exception('An invalid pet was selected.');
            }
            if ($pet->pet->category && !$pet->pet->category->allow_attach) {
                throw new \Exception('This pet is in a category that cannot be attached to a character.');
            }
            if ($pet->user_id != $user->id && !$user->hasPower('edit_inventories')) {
                throw new \Exception('You do not own this pet.');
            }

            // Next, check if the character the pet is being attached to is valid and the user has permission to attach the pet to that character.
            if (!$id) {
                throw new \Exception('No character selected.');
            }
            $character = Character::find($id);
            if (!$character) {
                throw new \Exception('An invalid character was selected.');
            }
            if ($character->user_id != $user->id && !$user->hasPower('edit_inventories')) {
                throw new \Exception('You do not own this character.');
            }

            // Finally, compare character and limits based on pet and pet category.
            $allPets = $character->pets;
            if ($pet->pet->category) {
                $petCategory = $pet->pet->category;
                $categoryLimit = $petCategory->limit;
                $categoryCount = 0;
                foreach ($allPets as $p) {
                    if ($p->pet->pet_category_id == $petCategory->id) {
                        $categoryCount++;
                    }
                }
                if ($categoryLimit && $categoryCount >= $categoryLimit) {
                    throw new \Exception('This character has reached the limit of pets in this category.');
                }
            }
            if ($pet->pet->limit) {
                $petLimit = $pet->pet->limit;
                $petCount = 0;
                foreach ($allPets as $p) {
                    if ($p->pet_id == $pet->pet->id) {
                        $petCount++;
                    }
                }
                if ($petLimit && $petCount >= $petLimit) {
                    throw new \Exception('This character has reached the limit of this pet.');
                }
            }

            // If all checks pass, attach the pet to the character.
            $pet->character_id = $character->id;
            $pet->attached_at = Carbon::now();
            $pet->save();

            if (!$pet->level && config('lorekeeper.pet_bonding_enabled')) {
                $pet->level()->create([
                    'bonding_level'   => 0,
                    'bonding'         => 0,
                ]);
            }

            return $this->commitReturn(true);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }

    /**
     * detaches a pet stack.
     *
     * @param mixed $pet
     */
    public function detachStack($pet) {
        DB::beginTransaction();

        try {
            $user = Auth::user();
            if (!$user->hasAlias) {
                throw new \Exception('Your deviantART account must be verified before you can perform this action.');
            }
            if (!$pet) {
                throw new \Exception('An invalid pet was selected.');
            }
            if ($pet->user_id != $user->id && !$user->hasPower('edit_inventories')) {
                throw new \Exception('You do not own this pet.');
            }

            $pet['character_id'] = null;
            $pet->save();

            return $this->commitReturn(true);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }

    /**
     * Bonds with a pet.
     *
     * @param mixed $pet
     * @param mixed $user
     */
    public function bondPet($pet, $user) {
        DB::beginTransaction();

        try {
            if (!config('lorekeeper.pets.pet_bonding_enabled')) {
                throw new \Exception('Pet bonding is not enabled.');
            }

            if ($user->id != $pet->user_id) {
                throw new \Exception('You do not own this pet.');
            }

            $pet->bonded_at = Carbon::now();
            $pet->save();

            if (!$pet->level) {
                $pet->level()->create([
                    'bonding_level'   => 0,
                    'bonding'         => 0,
                ]);
                $pet = $pet->fresh();
            }

            $bonding = $pet->level->bonding + 1;
            // check if meets bonding requirement for next level
            if ($pet->level->nextLevel && $bonding >= $pet->level->nextLevel?->bonding_required) {
                // check if this level has rewards, or if it has pet rewards for this pet
                $nextLevel = $pet->level->nextLevel;
                $nextLevelRewards = $pet->level->nextLevel->rewards;
                $petRewards = $nextLevel->pets()->where('pet_id', $pet->pet->id)->first()->rewards;
                if ($nextLevelRewards || $petRewards) {
                    $assets = createAssetsArray();

                    if ($nextLevelRewards) {
                        foreach ($nextLevelRewards as $reward) {
                            addAsset($assets, findReward($reward->rewardable_type, $reward->rewardable_id), $reward->quantity);
                        }
                    }

                    if ($petRewards) {
                        foreach ($petRewards as $reward) {
                            addAsset($assets, findReward($reward->rewardable_type, $reward->rewardable_id), $reward->quantity);
                        }
                    }

                    // function fillUserAssets($assets, $sender, $recipient, $logType, $data) {
                    fillUserAssets($assets, null, $pet->user, 'Pet Level Up', ['data' => 'Received rewards from leveling up '.$pet->pet->name]);

                    flash('You received: '.createRewardsString($assets))->success();
                }

                // level up
                $pet->level->bonding_level += 1;
                $pet->level->bonding = 0;
                $pet->level->save();

                flash('Your pet has leveled up! They are now level '.$pet->level->level->level.'.')->success();
            } else {
                $pet->level->bonding = $bonding;
                $pet->level->save();
            }

            return $this->commitReturn(true);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }

    /**
     * edits variant.
     *
     * @param mixed $id
     * @param mixed $pet
     * @param mixed $stack_id
     * @param mixed $isStaff
     */
    public function editVariant($id, $pet, $stack_id, $isStaff = false) {
        DB::beginTransaction();

        try {
            if (!$isStaff || !Auth::user()->isStaff) {
                if (!$stack_id) {
                    throw new \Exception('No item selected.');
                }

                if ($id == 0) {
                    $id = 'default';
                }

                // check if user has item
                $item = UserItem::find($stack_id);
                $tag = $item->item->tags->where('tag', 'splice')->first();
                if (!$tag) {
                    throw new \Exception('Item is not a splice.');
                }
                if ($tag->data['variant_ids'] && !in_array($id, $tag->data['variant_ids'])) {
                    throw new \Exception('Item is not a splice for this variant.');
                }
                if ($id == $pet->variant_id) {
                    throw new \Exception('Pet is already this variant.');
                }

                $invman = new InventoryManager;
                if (!$invman->debitStack($pet->user, 'Used to change pet variant', ['data' => 'Used to change '.$pet->pet->name.' variant'], $item, 1)) {
                    throw new \Exception('Could not debit item.');
                }
            }
            // else logAdminAction($pet->user, 'Pet Variant Changed', ['pet' => $pet->id, 'variant' => $id]); // for when develop is merged

            $pet['variant_id'] = $id == 'default' ? null : $id;
            $pet->save();

            return $this->commitReturn(true);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }

    /**
     * edits evolution.
     *
     * @param mixed $id
     * @param mixed $pet
     * @param mixed $stack_id
     * @param mixed $isStaff
     */
    public function editEvolution($id, $pet, $stack_id, $isStaff = false) {
        DB::beginTransaction();

        try {
            if (!$isStaff || !Auth::user()->isStaff) {
                if (!$stack_id) {
                    throw new \Exception('No item selected.');
                }

                // check if user has item
                $item = UserItem::find($stack_id);
                $invman = new InventoryManager;
                if (!$invman->debitStack($pet->user, 'Used to change pet evolution', ['data' => 'Used to change '.$pet->pet->name.' evolution'], $item, 1)) {
                    throw new \Exception('Could not debit item.');
                }
            }
            // else logAdminAction($pet->user, 'Pet Evolution Changed', ['pet' => $pet->id, 'evolution' => $id]); // for when develop is merged

            $pet['evolution_id'] = $id;
            $pet->save();

            return $this->commitReturn(true);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }

    /**
     * Edits the custom variant image on a user pet stack.
     *
     * @param mixed $pet
     * @param mixed $data
     */
    public function editCustomImage($pet, $data) {
        DB::beginTransaction();

        try {
            $data['has_image'] = 1;
            $image = null;
            if (isset($data['remove_image'])) {
                if ($pet && $pet->has_image && $data['remove_image']) {
                    $data['has_image'] = 0;
                    if (file_exists($pet->imagePath.'/'.$pet->imageFileName)) {
                        $this->deleteImage($pet->imagePath, $pet->imageFileName);
                    }
                }
                unset($data['remove_image']);
                unset($data['image']);
                $data['has_image'] = 0;
            }

            if (isset($data['image']) && $data['image']) {
                $image = $data['image'];
                unset($data['image']);
                $data['has_image'] = 1;
            }

            $data['artist_id'] = (isset($data['remove_credit']) && $data['remove_credit']) ? null : ($data['artist_id'] ?? null);
            $data['artist_url'] = (isset($data['remove_credit']) && $data['remove_credit']) ? null : ($data['artist_url'] ?? null);

            $pet->update($data);

            if ($pet) {
                $this->handleImage($image, $pet->imagePath, $pet->imageFileName);
            }

            return $this->commitReturn(true);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }

    /**
     * change pet's description.
     *
     * @param mixed $pet
     * @param mixed $data
     */
    public function editCustomImageDescription($pet, $data) {
        DB::beginTransaction();

        try {
            $pet->description = parse($data['description']);
            $pet->save();

            return $this->commitReturn(true);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }

    /**
     * Credits an pet to a user.
     *
     * @param User       $sender
     * @param User       $recipient
     * @param string     $type
     * @param array      $data
     * @param Pet        $pet
     * @param int        $quantity
     * @param mixed      $variant_id
     * @param mixed|null $evolution_id
     *
     * @return bool
     */
    public function creditPet($sender, $recipient, $type, $data, $pet, $quantity, $variant_id = null, $evolution_id = null) {
        DB::beginTransaction();

        try {
            for ($i = 0; $i < $quantity; $i++) {
                if ($variant_id == 'randomize' && count($pet->variants)) {
                    // randomly get a variant
                    $variant = $pet->variants->random();
                    // 25% chance to be no variant
                    if (rand(1, 4) == 1) {
                        $variant = null;
                    }
                } elseif ($variant_id == 'none') {
                    $variant = null;
                } else {
                    $variant = $pet->variants->where('id', $variant_id)->first();
                }

                if ($evolution_id == 'randomize' && count($pet->evolutions)) {
                    // randomly get an evolution
                    $evolution = $pet->evolutions->random();
                    // 25% chance to be no evolution
                    if (rand(1, 4) == 1) {
                        $evolution = null;
                    }
                } elseif ($evolution_id == 'none') {
                    $evolution = null;
                } else {
                    $evolution = $pet->evolutions->where('id', $evolution_id)->first();
                }

                $user_pet = UserPet::create([
                    'user_id'      => $recipient->id,
                    'pet_id'       => $pet->id,
                    'data'         => json_encode($data),
                    'variant_id'   => $variant?->id,
                    'evolution_id' => $evolution?->id,
                ]);
            }

            // Create drop information for the pet, if relevant
            if ($pet->hasDrops) {
                $drop = PetDrop::create([
                    'drop_id'         => $user_pet->pet->dropData->id,
                    'user_pet_id'     => $user_pet->id,
                    'parameters'      => $user_pet->pet->dropData->rollParameters(),
                    'drops_available' => 0,
                    'next_day'        => Carbon::now()
                        ->add($user_pet->pet->dropData->frequency, $user_pet->pet->dropData->interval)
                        ->startOf($user_pet->pet->dropData->interval),
                ]);
                if (!$drop) {
                    throw new \Exception('Failed to create drop.');
                }
            }

            if ($type && !$this->createLog($sender ? $sender->id : null, $recipient->id, null, $type, $data['data'], $pet->id, $quantity)) {
                throw new \Exception('Failed to create log.');
            }

            return $this->commitReturn(true);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }

    /**
     * Moves an pet stack from one user to another.
     *
     * @param User   $sender
     * @param User   $recipient
     * @param string $type
     * @param array  $data
     * @param mixed  $stack
     *
     * @return bool
     */
    public function moveStack($sender, $recipient, $type, $data, $stack) {
        DB::beginTransaction();

        try {
            $stack->user_id = $recipient->id;
            $stack->save();

            if ($type && !$this->createLog($sender ? $sender->id : null, $recipient->id, $stack->id, $type, $data['data'], $stack->pet_id, 1)) {
                throw new \Exception('Failed to create log.');
            }

            return $this->commitReturn(true);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }

    /**
     * Debits an pet from a user.
     *
     * @param User                    $user
     * @param string                  $type
     * @param array                   $data
     * @param \App\Models\Pet\UserPet $stack
     *
     * @return bool
     */
    public function debitStack($user, $type, $data, $stack) {
        DB::beginTransaction();

        try {
            $stack->delete();

            if ($type && !$this->createLog($user ? $user->id : null, null, $stack->id, $type, $data['data'], $stack->pet_id, 1)) {
                throw new \Exception('Failed to create log.');
            }

            return $this->commitReturn(true);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }

    /**
     * Creates an inventory log.
     *
     * @param int    $senderId
     * @param int    $recipientId
     * @param int    $stackId
     * @param string $type
     * @param string $data
     * @param int    $quantity
     * @param mixed  $petId
     *
     * @return int
     */
    public function createLog($senderId, $recipientId, $stackId, $type, $data, $petId, $quantity) {
        return DB::table('user_pets_log')->insert(
            [
                'sender_id'    => $senderId,
                'recipient_id' => $recipientId,
                'stack_id'     => $stackId,
                'log'          => $type.($data ? ' ('.$data.')' : ''),
                'log_type'     => $type,
                'data'         => $data, // this should be just a string
                'pet_id'       => $petId,
                'quantity'     => $quantity,
                'created_at'   => Carbon::now(),
                'updated_at'   => Carbon::now(),
            ]
        );
    }
}
