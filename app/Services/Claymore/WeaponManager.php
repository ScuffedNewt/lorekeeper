<?php

namespace App\Services\Claymore;

use App\Facades\Notifications;
use App\Models\Character\Character;
use App\Models\Claymore\Weapon;
use App\Models\User\User;
use App\Models\User\UserWeapon;
use App\Services\LimitManager;
use App\Services\Service;
use Carbon\Carbon;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class WeaponManager extends Service {
    /*
    |--------------------------------------------------------------------------
    | Inventory Manager
    |--------------------------------------------------------------------------
    |
    | Handles modification of user-owned weapons.
    |
    */

    /**
     * Grants an weapon to multiple users.
     *
     * @param array $data
     * @param User  $staff
     *
     * @return bool
     */
    public function grantWeapons($data, $staff) {
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
            array_walk($data['weapon_ids'], function ($id, $key) use (&$keyed_quantities, $data) {
                if ($id != null && !in_array($id, array_keys($keyed_quantities), true)) {
                    $keyed_quantities[$id] = $data['quantities'][$key];
                }
            });

            // Process weapon
            $weapons = Weapon::find($data['weapon_ids']);
            if (!count($weapons)) {
                throw new \Exception('No valid weapons found.');
            }

            foreach ($users as $user) {
                foreach ($weapons as $weapon) {
                    if ($this->creditWeapon($staff, $user, 'Staff Grant', Arr::only($data, ['data', 'disallow_transfer', 'notes']), $weapon, $keyed_quantities[$weapon->id])) {
                        Notifications::create('WEAPON_GRANT', $user, [
                            'weapon_name'     => $weapon->name,
                            'weapon_quantity' => $keyed_quantities[$weapon->id],
                            'sender_url'      => $staff->url,
                            'sender_name'     => $staff->name,
                        ]);
                    } else {
                        throw new \Exception('Failed to credit weapons to '.$user->name.'.');
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
     * Transfers an weapon stack between users.
     *
     * @param User       $sender
     * @param User       $recipient
     * @param UserWeapon $stack
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
                throw new \Exception('Invalid weapon selected.');
            }
            if ($stack->user_id != $sender->id && !$sender->hasPower('edit_inventories')) {
                throw new \Exception('You do not own this weapon.');
            }
            if ($stack->user_id == $recipient->id) {
                throw new \Exception("Cannot send an weapon to the weapon's owner.");
            }
            if (!$recipient) {
                throw new \Exception('Invalid recipient selected.');
            }
            if (!$recipient->hasAlias) {
                throw new \Exception('Cannot transfer weapons to a non-verified member.');
            }
            if ($recipient->is_banned) {
                throw new \Exception('Cannot transfer weapons to a banned member.');
            }
            if ((!$stack->weapon->allow_transfer || isset($stack->data['disallow_transfer'])) && !$sender->hasPower('edit_inventories')) {
                throw new \Exception('This weapon cannot be transferred.');
            }

            $oldUser = $stack->user;
            if ($this->moveStack($stack->user, $recipient, ($stack->user_id == $sender->id ? 'User Transfer' : 'Staff Transfer'), ['data' => ($stack->user_id != $sender->id ? 'Transferred by '.$sender->displayName : '')], $stack)) {
                Notifications::create('WEAPON_TRANSFER', $recipient, [
                    'weapon_name'     => $stack->weapon->name,
                    'weapon_quantity' => 1,
                    'sender_url'      => $sender->url,
                    'sender_name'     => $sender->name,
                ]);
                if ($stack->user_id != $sender->id) {
                    Notifications::create('FORCED_WEAPON_TRANSFER', $oldUser, [
                        'weapon_name'     => $stack->weapon->name,
                        'weapon_quantity' => 1,
                        'sender_url'      => $sender->url,
                        'sender_name'     => $sender->name,
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
     * Deletes an weapon stack.
     *
     * @param User       $user
     * @param UserWeapon $stack
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
                throw new \Exception('Invalid weapon selected.');
            }
            if ($stack->user_id != $user->id && !$user->hasPower('edit_inventories')) {
                throw new \Exception('You do not own this weapon.');
            }

            $oldUser = $stack->user;

            if ($this->debitStack($stack->user, ($stack->user_id == $user->id ? 'User Deleted' : 'Staff Deleted'), ['data' => ($stack->user_id != $user->id ? 'Deleted by '.$user->displayName : '')], $stack)) {
                if ($stack->user_id != $user->id) {
                    Notifications::create('WEAPON_REMOVAL', $oldUser, [
                        'weapon_name'     => $stack->weapon->name,
                        'weapon_quantity' => 1,
                        'sender_url'      => $user->url,
                        'sender_name'     => $user->name,
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
     * Names a weapon stack.
     *
     * @param  UserWeapon
     * @param mixed $weapon
     * @param mixed $name
     *
     * @return bool
     */
    public function nameStack($weapon, $name) {
        DB::beginTransaction();

        try {
            $user = Auth::user();
            if (!$user->hasAlias) {
                throw new \Exception('Your deviantART account must be verified before you can perform this action.');
            }
            if (!$weapon) {
                throw new \Exception('An invalid weapon was selected.');
            }
            if ($weapon->user_id != $user->id && !$user->hasPower('edit_inventories')) {
                throw new \Exception('You do not own this weapon.');
            }

            $weapon['weapon_name'] = $name;
            $weapon->save();

            return $this->commitReturn(true);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }

    /**
     * attaches a weapon stack.
     *
     * @param mixed $weapon
     * @param mixed $id
     *
     * @return bool
     */
    public function attachStack($weapon, $id) {
        DB::beginTransaction();

        try {
            $user = Auth::user();
            if ($id == null) {
                throw new \Exception('No character selected.');
            }
            $character = Character::find($id);
            if (!$user->hasAlias) {
                throw new \Exception('Your deviantART account must be verified before you can perform this action.');
            }
            if (!$weapon) {
                throw new \Exception('An invalid weapon was selected.');
            }
            if ($weapon->user_id != $user->id && !$user->hasPower('edit_inventories')) {
                throw new \Exception('You do not own this weapon.');
            }
            if (!$character) {
                throw new \Exception('An invalid character was selected.');
            }
            if ($character->user_id !== $user->id && !$user->hasPower('edit_inventories')) {
                throw new \Exception('You do not own this character.');
            }

            if (isset($weapon->weapon->category->class_restriction)) {
                if (!$character->class_id || $character->class_id != $weapon->weapon->category->class_restriction) {
                    throw new \Exception('This character does not have the right class to hold this weapon.');
                }
            }

            if ($character->weapons()->where('weapon_id', $weapon->weapon->id)->exists()) {
                throw new \Exception('This type of weapon is already attached to the character selected.');
            }
            foreach ($character->weapons as $cwea) {
                if ($cwea->weapon->weapon_category_id == $weapon->weapon->weapon_category_id) {
                    throw new \Exception('This type of weapon is already attached to the character selected.');
                }
            }

            $weapon['character_id'] = $character->id;
            $weapon['attached_at'] = Carbon::now();
            $weapon->save();

            return $this->commitReturn(true);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }

    /**
     * detaches a weapon stack.
     *
     * @param mixed $weapon
     */
    public function detachStack($weapon) {
        DB::beginTransaction();

        try {
            $user = Auth::user();
            if (!$user->hasAlias) {
                throw new \Exception('Your deviantART account must be verified before you can perform this action.');
            }
            if (!$weapon) {
                throw new \Exception('An invalid weapon was selected.');
            }
            if ($weapon->user_id != $user->id && !$user->hasPower('edit_inventories')) {
                throw new \Exception('You do not own this weapon.');
            }

            $weapon['character_id'] = null;
            $weapon['attached_at'] = null;
            $weapon->save();

            return $this->commitReturn(true);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }

    /**
     * @param UserWeapon
     * @param mixed $weapon
     * @param mixed $isStaff
     * @param mixed $child_id
     *
     * @return UserWeapon
     */
    public function upgradeWeapon($weapon, $child_id, $isStaff = false) {
        DB::beginTransaction();

        try {
            $childWeapon = Weapon::find($child_id);
            if (!$childWeapon) {
                throw new \Exception('Invalid weapon selected.');
            }

            if ($childWeapon->parent_id != $weapon->weapon_id) {
                throw new \Exception('Invalid weapon selected.');
            }

            if (!$isStaff) {
                if ($weapon->user_id != Auth::user()->id && !$isStaff) {
                    throw new \Exception('You do not own this weapon.');
                }

                $limitData = [
                    'character_ids' => $weapon->character_id ? [$weapon->character_id] : [],
                ];

                if (count(getLimits($childWeapon))) {
                    $limitService = new LimitManager;
                    if (!$limitService->checkLimits($childWeapon, false, $limitData, 'Weapon Upgrade', 'Upgraded '.$weapon->weapon->displayName.' to '.$childWeapon->displayName)) {
                        foreach ($limitService->errors()->getMessages()['error'] as $error) {
                            flash($error)->error();
                        }

                        throw new \Exception('Failed to upgrade weapon.');
                    }
                }
            }

            $weapon->weapon_id = $child_id;
            $weapon->save();

            return $this->commitReturn(true);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }

    /**
     * Image -> gives unique image.
     *
     * @param mixed $weapon
     * @param mixed $data
     */
    public function addImage($weapon, $data) {
        DB::beginTransaction();

        try {
            $weapon->has_image = 1;
            $weapon->save();

            $image = null;
            if (isset($data['remove_image'])) {
                if ($weapon && $weapon->has_image && $data['remove_image']) {
                    $data['has_image'] = 0;
                    $this->deleteImage($weapon->categoryImagePath, $weapon->categoryImageFileName);
                }
                unset($data['remove_image']);
            }

            if (isset($data['image']) && $data['image']) {
                $data['has_image'] = 1;
                $image = $data['image'];
                unset($data['image']);
            } else {
                $data['has_image'] = 0;
            }

            $this->handleImage($image, $weapon->imagePath, $weapon->imageFileName);

            return $this->commitReturn(true);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }

    /**
     * Credits an weapon to a user.
     *
     * @param User                      $sender
     * @param User                      $recipient
     * @param string                    $type
     * @param array                     $data
     * @param \App\Models\Weapon\Weapon $weapon
     * @param int                       $quantity
     *
     * @return bool
     */
    public function creditWeapon($sender, $recipient, $type, $data, $weapon, $quantity) {
        DB::beginTransaction();

        try {
            for ($i = 0; $i < $quantity; $i++) {
                UserWeapon::create(['user_id' => $recipient->id, 'weapon_id' => $weapon->id, 'data' => json_encode($data), 'attached_at' => null]);
            }
            if ($type && !$this->createLog($sender ? $sender->id : null, $recipient->id, null, $type, $data['data'], $weapon->id, $quantity)) {
                throw new \Exception('Failed to create log.');
            }

            return $this->commitReturn(true);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }

    /**
     * Moves an weapon stack from one user to another.
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

            if ($type && !$this->createLog($sender ? $sender->id : null, $recipient->id, $stack->id, $type, $data['data'], $stack->weapon_id, 1)) {
                throw new \Exception('Failed to create log.');
            }

            return $this->commitReturn(true);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }

    /**
     * Debits an weapon from a user.
     *
     * @param User                          $user
     * @param string                        $type
     * @param array                         $data
     * @param \App\Models\Weapon\UserWeapon $stack
     *
     * @return bool
     */
    public function debitStack($user, $type, $data, $stack) {
        DB::beginTransaction();

        try {
            $stack->delete();

            if ($type && !$this->createLog($user ? $user->id : null, null, $stack->id, $type, $data['data'], $stack->weapon_id, 1)) {
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
     * @param mixed  $weaponId
     *
     * @return int
     */
    public function createLog($senderId, $recipientId, $stackId, $type, $data, $weaponId, $quantity) {
        return DB::table('user_weapons_log')->insert(
            [
                'sender_id'    => $senderId,
                'recipient_id' => $recipientId,
                'stack_id'     => $stackId,
                'log'          => $type.($data ? ' ('.$data.')' : ''),
                'log_type'     => $type,
                'data'         => $data, // this should be just a string
                'weapon_id'    => $weaponId,
                'quantity'     => $quantity,
                'created_at'   => Carbon::now(),
                'updated_at'   => Carbon::now(),
            ]
        );
    }
}
