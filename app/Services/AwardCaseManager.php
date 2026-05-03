<?php

namespace App\Services;

use App\Models\Award\Award;
use App\Models\Character\Character;
use App\Models\Character\CharacterAward;
use App\Models\Currency\Currency;
use App\Models\Item\Item;
use App\Models\User\User;
use App\Models\User\UserAward;
use App\Models\User\UserItem;
use Arr;
use Auth;
use Carbon\Carbon;
use DB;
use Notifications;

class AwardCaseManager extends Service {
    /*
    |--------------------------------------------------------------------------
    | Awardcase Manager
    |--------------------------------------------------------------------------
    |
    | Handles modification of user-owned awards.
    |
    */

    /**
     * Grants an award to multiple users.
     *
     * @param array $data
     * @param User  $staff
     *
     * @return bool
     */
    public function grantAwards($data, $staff) {
        DB::beginTransaction();

        try {
            $users = null;
            $characters = null;
            // Process targets. Actually uses ids but using naming method of users to keep things simple.
            if (isset($data['names'])) {
                $users = User::find($data['names']);
                if (count($users) != count($data['names'])) {
                    throw new \Exception('An invalid user was selected.');
                }
            }
            if (isset($data['character_names'])) {
                $characters = Character::find($data['character_names']);
                if (count($characters) != count($data['character_names'])) {
                    throw new \Exception('An invalid character was selected.');
                }
            }

            foreach ([$users, $characters] as $targets) {
                if (!$targets) {
                    continue;
                }
                $type = $targets->first()->logType;
                $ids = (($type == 'User') ? $data['award_ids'] : $data['character_award_ids']);
                $quantities = (($type == 'User') ? $data['quantities'] : $data['character_quantities']);

                $keyed_quantities = [];
                array_walk($ids, function ($id, $key) use (&$keyed_quantities, $quantities) {
                    if ($id != null && !in_array($id, array_keys($keyed_quantities), true)) {
                        $keyed_quantities[$id] = $quantities[$key];
                    }
                });

                // Process award
                $awards = Award::find((($type == 'User') ? $data['award_ids'] : $data['character_award_ids']));
                if (!count($awards)) {
                    throw new \Exception('No valid awards found.');
                }

                foreach ($targets as $target) {
                    foreach ($awards as $award) {
                        if ($this->creditAward($staff, $target, 'Staff Grant', array_only($data, ['data', 'disallow_transfer', 'notes']), $award, $keyed_quantities[$award->id])) {
                            if ($type == 'User') {
                                Notifications::create('AWARD_GRANT', $target, [
                                    'award_name'     => $award->name,
                                    'award_quantity' => $keyed_quantities[$award->id],
                                    'sender_url'     => $staff->url,
                                    'sender_name'    => $staff->name,
                                ]);
                            } else {
                                Notifications::create('CHARACTER_AWARD_GRANT', $target->user, [
                                    'award_name'     => $award->name,
                                    'award_quantity' => $keyed_quantities[$award->id],
                                    'sender_url'     => $staff->url,
                                    'sender_name'    => $staff->name,
                                    'character_name' => $target->fullName,
                                    'character_slug' => $target->slug,
                                ]);
                            }
                        } else {
                            throw new \Exception('Failed to credit awards to '.$user->name.'.');
                        }
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
     * Grants an award to a character.
     *
     * @param array     $data
     * @param Character $character
     * @param User      $staff
     *
     * @return bool
     */
    public function grantCharacterAwards($data, $character, $staff) {
        DB::beginTransaction();

        try {
            if (!$character) {
                throw new \Exception('Invalid character selected.');
            }

            foreach ($data['quantities'] as $q) {
                if ($q <= 0) {
                    throw new \Exception('All quantities must be at least 1.');
                }
            }

            $keyed_quantities = [];
            array_walk($data['award_ids'], function ($id, $key) use (&$keyed_quantities, $data) {
                if ($id != null && !in_array($id, array_keys($keyed_quantities), true)) {
                    $keyed_quantities[$id] = $data['quantities'][$key];
                }
            });

            // Process award(s)
            $awards = Award::find($data['award_ids']);
            foreach ($awards as $i) {
                if (!$i->is_character_owned) {
                    throw new \Exception('One of these awards cannot be owned by characters.');
                }
            }
            if (!count($awards)) {
                throw new \Exception('No valid awards found.');
            }

            foreach ($awards as $award) {
                $this->creditAward($staff, $character, 'Staff Grant', Arr::only($data, ['data', 'disallow_transfer', 'notes']), $award, $keyed_quantities[$award->id]);
                if ($character->is_visible && $character->user_id) {
                    Notifications::create('CHARACTER_AWARD_GRANT', $character->user, [
                        'award_name'     => $award->name,
                        'award_quantity' => $keyed_quantities[$award->id],
                        'sender_url'     => $staff->url,
                        'sender_name'    => $staff->name,
                        'character_name' => $character->fullName,
                        'character_slug' => $character->slug,
                    ]);
                }
            }

            return $this->commitReturn(true);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }

    /**
     * Transfers awards between a user and character.
     *
     * @param Character|User           $sender
     * @param Character|User           $recipient
     * @param CharacterAward|UserAward $stacks
     * @param int                      $quantities
     *
     * @return bool
     */
    public function transferCharacterStack($sender, $recipient, $stacks, $quantities) {
        DB::beginTransaction();

        try {
            foreach ($stacks as $key=>$stack) {
                $quantity = $quantities[$key];

                if (!$stack) {
                    throw new \Exception('Invalid or no stack selected.');
                }
                if (!$recipient) {
                    throw new \Exception('Invalid recipient selected.');
                }
                if (!$sender) {
                    throw new \Exception('Invalid sender selected.');
                }

                if ($recipient->logType == 'Character' && $sender->logType == 'Character') {
                    throw new \Exception('Cannot transfer awards between characters.');
                }
                if ($recipient->logType == 'Character' && !$sender->hasPower('edit_inventories') && !$recipient->is_visible) {
                    throw new \Exception('Invalid character selected.');
                }
                if (!$stacks) {
                    throw new \Exception('Invalid stack selected.');
                }
                if ($sender->logType == 'Character' && $quantity <= 0 && $stack->count > 0) {
                    $quantity = $stack->count;
                }
                if ($quantity <= 0) {
                    throw new \Exception('Invalid quantity entered.');
                }

                if (($recipient->logType == 'Character' && !$sender->hasPower('edit_inventories') && !Auth::user() == $recipient->user) || ($recipient->logType == 'User' && !Auth::user()->hasPower('edit_inventories') && !Auth::user() == $sender->user)) {
                    throw new \Exception('Cannot transfer awards to/from a character you don\'t own.');
                }

                if ($recipient->logType == 'Character' && !$stack->award->is_character_owned) {
                    throw new \Exception('One of the selected awards cannot be owned by characters.');
                }
                if ((!$stack->award->allow_transfer || isset($stack->data['disallow_transfer'])) && !Auth::user()->hasPower('edit_inventories')) {
                    throw new \Exception('One of the selected awards cannot be transferred.');
                }
                if ($stack->count < $quantity) {
                    throw new \Exception('Quantity to transfer exceeds award count.');
                }

                // Check that hold count isn't being exceeded
                if ($stack->award->character_limit > 0) {
                    $limit = $stack->award->character_limit;
                }
                if ($recipient->logType == 'Character' && isset($limit)) {
                    $ownedLimitedAwards = CharacterAward::with('award')->where('award_id', $stack->award->id)->whereNull('deleted_at')->where('count', '>', '0')->where('character_id', $recipient->id)->get();
                    $newOwnedLimit = $ownedLimitedAwards->pluck('count')->sum() + $quantity;
                }

                if ($recipient->logType == 'Character' && isset($limit) && ($ownedLimitedAwards->pluck('count')->sum() >= $limit || $newOwnedLimit > $limit)) {
                    throw new \Exception('This exceeds the limit a character can own.');
                }

                $this->creditAward($sender, $recipient, $sender->logType == 'User' ? 'User → Character Transfer' : 'Character → User Transfer', $stack->data, $stack->award, $quantity);

                $stack->count -= $quantity;
                $stack->save();
            }

            return $this->commitReturn(true);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }

    /**
     * Transfers awards between user stacks.
     *
     * @param User      $sender
     * @param User      $recipient
     * @param UserAward $stacks
     * @param int       $quantities
     *
     * @return bool
     */
    public function transferStack($sender, $recipient, $stacks, $quantities) {
        DB::beginTransaction();

        try {
            foreach ($stacks as $key=>$stack) {
                $quantity = $quantities[$key];
                if (!$sender->hasAlias) {
                    throw new \Exception('You need to have a linked social media account before you can perform this action.');
                }
                if (!$stack) {
                    throw new \Exception('Transfer failed: An invalid award was selected.');
                }
                if (!$recipient) {
                    throw new \Exception('Transfer failed: Invalid recipient selected.');
                }
                if ($stack->user_id != $sender->id && !$sender->hasPower('edit_inventories')) {
                    throw new \Exception('Transfer failed: You do not own one of the selected awards.');
                }
                if ($stack->user_id == $recipient->id) {
                    throw new \Exception('Transfer failed: Cannot send awards to the award\'s owner.');
                }
                if (!$recipient->hasAlias) {
                    throw new \Exception('Transfer failed: Cannot transfer awards to a non-verified member.');
                }
                if ($recipient->is_banned) {
                    throw new \Exception('Transfer failed: Cannot transfer awards to a banned member.');
                }
                if ((!$stack->award->allow_transfer || isset($stack->data['disallow_transfer'])) && !$sender->hasPower('edit_inventories')) {
                    throw new \Exception('Transfer failed: One of the selected awards cannot be transferred.');
                }
                if ($stack->count < $quantity) {
                    throw new \Exception('Transfer failed: Quantity to transfer exceeds award count.');
                }

                // Check to ensure it doesn't go over the limit.
                $count = 0;
                $recipientAwards = $recipient->awards->where('id', $stack->award_id);
                if ($recipientAwards) {
                    foreach ($recipientAwards as $award) {
                        $count += $award->pivot->count;
                    }
                }
                if ($award->user_limit && (($count + $quantity) > $award->user_limit)) {
                    throw new \Exception('Transfer failed: This would take the recipient over their limit for this award.');
                }

                $oldUser = $stack->user;
                if ($this->moveStack($stack->user, $recipient, ($stack->user_id == $sender->id ? 'User Transfer' : 'Staff Transfer'), ['data' => ($stack->user_id != $sender->id ? 'Transferred by '.$sender->displayName : '')], $stack, $quantity)) {
                    Notifications::create('AWARD_TRANSFER', $recipient, [
                        'award_name'     => $stack->award->name,
                        'award_quantity' => $quantity,
                        'sender_url'     => $sender->url,
                        'sender_name'    => $sender->name,
                    ]);
                    if ($stack->user_id != $sender->id) {
                        Notifications::create('FORCED_AWARD_TRANSFER', $oldUser, [
                            'award_name'     => $stack->award->name,
                            'award_quantity' => $quantity,
                            'sender_url'     => $sender->url,
                            'sender_name'    => $sender->name,
                        ]);
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
     * Deletes awards from stack.
     *
     * @param Character|User           $owner
     * @param CharacterAward|UserAward $stacks
     * @param int                      $quantities
     *
     * @return bool
     */
    public function deleteStack($owner, $stacks, $quantities) {
        DB::beginTransaction();

        try {
            if ($owner->logType == 'User') {
                foreach ($stacks as $key=>$stack) {
                    $user = Auth::user();
                    $quantity = $quantities[$key];
                    if (!$owner->hasAlias) {
                        throw new \Exception('You need to have a linked social media account before you can perform this action.');
                    }
                    if (!$stack) {
                        throw new \Exception('An invalid award was selected.');
                    }
                    if ($stack->user_id != $owner->id && !$user->hasPower('edit_inventories')) {
                        throw new \Exception('You do not own one of the selected awards.');
                    }
                    if ($stack->count < $quantity) {
                        throw new \Exception('Quantity to delete exceeds award count.');
                    }

                    $oldUser = $stack->user;

                    if ($this->debitStack($stack->user, ($stack->user_id == $user->id ? 'User Deleted' : 'Staff Deleted'), ['data' => ($stack->user_id != $user->id ? 'Deleted by '.$user->displayName : '')], $stack, $quantity)) {
                        if ($stack->user_id != $user->id) {
                            Notifications::create('AWARD_REMOVAL', $oldUser, [
                                'award_name'     => $stack->award->name,
                                'award_quantity' => $quantity,
                                'sender_url'     => $user->url,
                                'sender_name'    => $user->name,
                            ]);
                        }
                    }
                }
            } else {
                foreach ($stacks as $key=>$stack) {
                    $quantity = $quantities[$key];
                    $user = Auth::user();
                    if (!$user->hasAlias) {
                        throw new \Exception('You need to have a linked social media account before you can perform this action.');
                    }
                    if (!$stack) {
                        throw new \Exception('An invalid award was selected.');
                    }
                    if ($stack->character->user_id != $user->id && !$user->hasPower('edit_inventories')) {
                        throw new \Exception('You do not own one of the selected award.');
                    }
                    if ($stack->count < $quantity) {
                        throw new \Exception('Quantity to delete exceeds award count.');
                    }

                    if ($this->debitStack($stack->character, ($stack->character->user_id == $user->id ? 'User Deleted' : 'Staff Deleted'), ['data' => ($stack->character->user_id != $user->id ? 'Deleted by '.$user->displayName : '')], $stack, $quantity)) {
                        if ($stack->character->user_id != $user->id && $stack->character->is_visible && $stack->character->user_id) {
                            Notifications::create('CHARACTER_AWARD_REMOVAL', $stack->character->user, [
                                'award_name'     => $stack->award->name,
                                'award_quantity' => $quantity,
                                'sender_url'     => $user->url,
                                'sender_name'    => $user->name,
                                'character_name' => $stack->character->fullName,
                                'character_slug' => $stack->character->slug,
                            ]);
                        }
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
     * Credits an award to a user or character.
     *
     * @param Character|User $sender
     * @param Character|User $recipient
     * @param string         $type
     * @param array          $data
     * @param Award          $award
     * @param int            $quantity
     *
     * @return bool
     */
    public function creditAward($sender, $recipient, $type, $data, $award, $quantity) {
        DB::beginTransaction();

        try {
            $encoded_data = \json_encode($data);

            if ($recipient->logType == 'User') {
                $recipient_stack = UserAward::where([
                    ['user_id', '=', $recipient->id],
                    ['award_id', '=', $award->id],
                    ['data', '=', $encoded_data], // this must be encoded since eloquent hasn't casted it yet
                ])->first();

                if (!$recipient_stack) {
                    $recipient_stack = UserAward::create(['user_id' => $recipient->id, 'award_id' => $award->id, 'data' => $data]);
                }

                // if the award has a limit, check that it isn't being exceeded, if it is, flash a message
                if ($award->user_limit > 0) {
                    $current_count = UserAward::where('user_id', $recipient->id)->where('award_id', $award->id)->whereNull('deleted_at')->sum('count');
                    if (($current_count + $quantity) > $award->user_limit) {
                        flash('Award '.$award->displayName.' has a limit of '.$award->user_limit.' per user. The recipient '.$recipient->displayName.'\'s award quantity would exceed this, so no modification was made to the recipient\'s award quantity.')->warning();

                        return $this->commitReturn(true);
                    } else {
                        $recipient_stack->count += $quantity;
                    }
                } else {
                    $recipient_stack->count += $quantity;
                }

                $recipient_stack->save();
            } else {
                $recipient_stack = CharacterAward::where([
                    ['character_id', '=', $recipient->id],
                    ['award_id', '=', $award->id],
                    ['data', '=', $encoded_data],
                ])->first();

                if (!$recipient_stack) {
                    $recipient_stack = CharacterAward::create(['character_id' => $recipient->id, 'award_id' => $award->id, 'data' => $data]);
                }

                // if the award has a limit, check that it isn't being exceeded, if it is, flash a message
                if ($award->user_limit > 0) {
                    $current_count = UserAward::where('user_id', $recipient->id)->where('award_id', $award->id)->whereNull('deleted_at')->sum('count');
                    if (($current_count + $quantity) > $award->user_limit) {
                        flash('Award '.$award->displayName.' has a limit of '.$award->user_limit.' per user. The recipient '.$recipient->displayName.'\'s award quantity would exceed this, so no modification was made to the recipient\'s award quantity.')->warning();

                        return $this->commitReturn(true);
                    } else {
                        $recipient_stack->count += $quantity;
                    }
                } else {
                    $recipient_stack->count += $quantity;
                }

                $recipient_stack->save();
            }

            if ($type && !$this->createLog($sender ? $sender->id : null, $sender ? $sender->logType : null, $recipient ? $recipient->id : null, $recipient ? $recipient->logType : null, null, $type, $data['data'], $award->id, $quantity)) {
                throw new \Exception('Failed to create log.');
            }

            return $this->commitReturn(true);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }

    /**
     * Moves awards from one user or character stack to another.
     *
     * @param Character|User $sender
     * @param Character|User $recipient
     * @param string         $type
     * @param array          $data
     * @param mixed          $stack
     * @param mixed          $quantity
     *
     * @return bool
     */
    public function moveStack($sender, $recipient, $type, $data, $stack, $quantity) {
        DB::beginTransaction();

        try {
            $recipient_stack = UserAward::where([
                ['user_id', '=', $recipient->id],
                ['award_id', '=', $stack->award_id],
                ['data', '=', json_encode($stack->data)],
            ])->first();

            if (!$recipient_stack) {
                $recipient_stack = UserAward::create(['user_id' => $recipient->id, 'award_id' => $stack->award_id, 'data' => $stack->data]);
            }

            $stack->count -= $quantity;
            $recipient_stack->count += $quantity;
            $stack->save();
            $recipient_stack->save();

            if ($type && !$this->createLog($sender ? $sender->id : null, $sender ? $sender->logType : null, $recipient->id, $recipient ? $recipient->logType : null, $stack->id, $type, $data['data'], $stack->award_id, $quantity)) {
                throw new \Exception('Failed to create log.');
            }

            return $this->commitReturn(true);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }

    /**
     * Debits an award from a user or character.
     *
     * @param Character|User              $owner
     * @param string                      $type
     * @param array                       $data
     * @param \App\Models\Award\UserAward $stack
     * @param mixed                       $quantity
     *
     * @return bool
     */
    public function debitStack($owner, $type, $data, $stack, $quantity) {
        DB::beginTransaction();

        try {
            $stack->count -= $quantity;
            $stack->save();

            if ($type && !$this->createLog($owner ? $owner->id : null, $owner ? $owner->logType : null, null, null, $stack->id, $type, $data['data'], $stack->award->id, $quantity)) {
                throw new \Exception('Failed to create log.');
            }

            return $this->commitReturn(true);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }

    /**
     * Creates an awardcase log.
     *
     * @param int    $senderId
     * @param string $senderType
     * @param int    $recipientId
     * @param string $recipientType
     * @param int    $stackId
     * @param string $type
     * @param string $data
     * @param int    $quantity
     * @param mixed  $awardId
     *
     * @return int
     */
    public function createLog($senderId, $senderType, $recipientId, $recipientType, $stackId, $type, $data, $awardId, $quantity) {
        return DB::table('awards_log')->insert(
            [
                'sender_id'      => $senderId,
                'sender_type'    => $senderType,
                'recipient_id'   => $recipientId,
                'recipient_type' => $recipientType,
                'stack_id'       => $stackId,
                'log'            => $type.($data ? ' ('.$data.')' : ''),
                'log_type'       => $type,
                'data'           => $data, // this should be just a string
                'award_id'       => $awardId,
                'quantity'       => $quantity,
                'created_at'     => Carbon::now(),
                'updated_at'     => Carbon::now(),
            ]
        );
    }

    /*****************************************************************************
     *
     * PROGRESSION STUFF
     *
     *****************************************************************************/

    /**
     * Claims an award if a user has all progressions.
     *
     * @param mixed $award
     * @param mixed $user
     */
    public function claimAward($award, $user) {
        DB::beginTransaction();

        try {
            $progressionData = [];
            foreach ($award->progressions as $progression) {
                if (!$progression->isUnlocked($user)) {
                    throw new \Exception('You do not have all the progressions required for this award.');
                }

                if (!isset($progressionData[$progression->type])) {
                    $progressionData[$progression->type] = [];
                }
                if (isset($progressionData[$progression->type][$progression->type_id])) {
                    $progressionData[$progression->type][$progression->type_id] += $progression->quantity;
                } else {
                    $progressionData[$progression->type][$progression->type_id] = $progression->quantity;
                }
            }

            // credit the award (if the user has the award already, we do not give them another one)
            if (!$user->awards()->where('award_id', $award->id)->first()) {
                if (!$this->creditAward($user, $user, 'Award Claim', ['data' => 'Received award by completing progessions', 'progression_data' => $progressionData], $award, 1)) {
                    throw new \Exception('Failed to credit award.');
                }
            }

            // grant the award rewards
            $assets = createAssetsArray();
            foreach ($award->rewards as $reward) {
                addAsset($assets, $reward->reward, $reward->quantity);
            }
            if (!$assets = fillUserAssets($assets, $user, $user, 'Award Claim', ['data' => 'Received award by completing progessions'])) {
                throw new \Exception('Failed to distribute rewards to user.');
            }

            flash('You received the following rewards along with the award: '.createRewardsString($assets));

            // debit all the progressions, if debit is enabled
            if ($award->debit_progressions) {
                $this->debitProgressions($user, $award);
            }

            return $this->commitReturn(true);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }

    /**
     * Debits progression requirements from a user when claiming an award.
     *
     * @param mixed $user
     * @param mixed $award
     *
     * @return bool
     */
    private function debitProgressions($user, $award) {
        foreach ($award->progressions as $loot) {
            $reward = null;
            switch ($loot->type) {
                case 'Item':
                    $reward = Item::find($loot->type_id);
                    $service = new InventoryManager;
                    // debit the item
                    $stack = UserItem::where('user_id', $user->id)->where('item_id', $reward->id)->where('count', '>', 0)->first();
                    if (!$service->debitStack($user, 'Award Claim', ['data' => 'Used in an award claim for '.$award->name], $stack, $loot->quantity)) {
                        throw new \Exception('Failed to debit item (you likely do not have enough).');
                    }
                    break;
                case 'Currency':
                    $reward = Currency::find($loot->type_id);
                    if (!$reward->is_user_owned) {
                        throw new \Exception('Invalid currency selected.');
                    }
                    $service = new CurrencyManager;
                    // debit the currency
                    if (!$service->debitCurrency($user, null, 'Award Claim', 'Used in an award claim for '.$award->name, $reward, $loot->quantity)) {
                        throw new \Exception('Failed to debit currency (you likely do not have enough).');
                    }
                    break;
                case 'Award':
                    $reward = Award::find($loot->type_id);
                    // debit the award
                    $stack = UserAward::where('user_id', $user->id)->where('award_id', $reward->id)->where('count', '>', 0)->first();
                    if (!$this->debitStack($user, 'Award Claim', ['data' => 'Used in an award claim for '.$award->name], $stack, $loot->quantity)) {
                        throw new \Exception('Failed to debit award (you likely do not have enough).');
                    }
                    break;
            }
        }
    }
}
