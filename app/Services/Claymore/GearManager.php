<?php

namespace App\Services\Claymore;

use App\Facades\Notifications;
use App\Models\Character\Character;
use App\Models\Claymore\Gear;
use App\Models\User\User;
use App\Models\User\UserGear;
use App\Services\LimitManager;
use App\Services\Service;
use Carbon\Carbon;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class GearManager extends Service {
    /*
    |--------------------------------------------------------------------------
    | Inventory Manager
    |--------------------------------------------------------------------------
    |
    | Handles modification of user-owned gears.
    |
    */

    /**
     * Grants an gear to multiple users.
     *
     * @param array $data
     * @param User  $staff
     *
     * @return bool
     */
    public function grantGears($data, $staff) {
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
            array_walk($data['gear_ids'], function ($id, $key) use (&$keyed_quantities, $data) {
                if ($id != null && !in_array($id, array_keys($keyed_quantities), true)) {
                    $keyed_quantities[$id] = $data['quantities'][$key];
                }
            });

            // Process gear
            $gears = Gear::find($data['gear_ids']);
            if (!count($gears)) {
                throw new \Exception('No valid gears found.');
            }

            foreach ($users as $user) {
                foreach ($gears as $gear) {
                    if ($this->creditGear($staff, $user, 'Staff Grant', Arr::only($data, ['data', 'disallow_transfer', 'notes']), $gear, $keyed_quantities[$gear->id])) {
                        Notifications::create('GEAR_GRANT', $user, [
                            'gear_name'     => $gear->name,
                            'gear_quantity' => $keyed_quantities[$gear->id],
                            'sender_url'    => $staff->url,
                            'sender_name'   => $staff->name,
                        ]);
                    } else {
                        throw new \Exception('Failed to credit gears to '.$user->name.'.');
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
     * Transfers an gear stack between users.
     *
     * @param User     $sender
     * @param User     $recipient
     * @param UserGear $stack
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
                throw new \Exception('Invalid gear selected.');
            }
            if ($stack->user_id != $sender->id && !$sender->hasPower('edit_inventories')) {
                throw new \Exception('You do not own this gear.');
            }
            if ($stack->user_id == $recipient->id) {
                throw new \Exception("Cannot send an gear to the gear's owner.");
            }
            if (!$recipient) {
                throw new \Exception('Invalid recipient selected.');
            }
            if (!$recipient->hasAlias) {
                throw new \Exception('Cannot transfer gears to a non-verified member.');
            }
            if ($recipient->is_banned) {
                throw new \Exception('Cannot transfer gears to a banned member.');
            }
            if ((!$stack->gear->allow_transfer || isset($stack->data['disallow_transfer'])) && !$sender->hasPower('edit_inventories')) {
                throw new \Exception('This gear cannot be transferred.');
            }

            $oldUser = $stack->user;
            if ($this->moveStack($stack->user, $recipient, ($stack->user_id == $sender->id ? 'User Transfer' : 'Staff Transfer'), ['data' => ($stack->user_id != $sender->id ? 'Transferred by '.$sender->displayName : '')], $stack)) {
                Notifications::create('GEAR_TRANSFER', $recipient, [
                    'gear_name'     => $stack->gear->name,
                    'gear_quantity' => 1,
                    'sender_url'    => $sender->url,
                    'sender_name'   => $sender->name,
                ]);
                if ($stack->user_id != $sender->id) {
                    Notifications::create('FORCED_GEAR_TRANSFER', $oldUser, [
                        'gear_name'     => $stack->gear->name,
                        'gear_quantity' => 1,
                        'sender_url'    => $sender->url,
                        'sender_name'   => $sender->name,
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
     * Deletes an gear stack.
     *
     * @param User     $user
     * @param UserGear $stack
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
                throw new \Exception('Invalid gear selected.');
            }
            if ($stack->user_id != $user->id && !$user->hasPower('edit_inventories')) {
                throw new \Exception('You do not own this gear.');
            }

            $oldUser = $stack->user;

            if ($this->debitStack($stack->user, ($stack->user_id == $user->id ? 'User Deleted' : 'Staff Deleted'), ['data' => ($stack->user_id != $user->id ? 'Deleted by '.$user->displayName : '')], $stack)) {
                if ($stack->user_id != $user->id) {
                    Notifications::create('GEAR_REMOVAL', $oldUser, [
                        'gear_name'     => $stack->gear->name,
                        'gear_quantity' => 1,
                        'sender_url'    => $user->url,
                        'sender_name'   => $user->name,
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
     * Names a gear stack.
     *
     * @param  UserGear
     * @param mixed $gear
     * @param mixed $name
     *
     * @return bool
     */
    public function nameStack($gear, $name) {
        DB::beginTransaction();

        try {
            $user = Auth::user();
            if (!$user->hasAlias) {
                throw new \Exception('Your deviantART account must be verified before you can perform this action.');
            }
            if (!$gear) {
                throw new \Exception('An invalid gear was selected.');
            }
            if ($gear->user_id != $user->id && !$user->hasPower('edit_inventories')) {
                throw new \Exception('You do not own this gear.');
            }

            $gear['gear_name'] = $name;
            $gear->save();

            return $this->commitReturn(true);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }

    /**
     * attaches a gear stack.
     *
     * @param mixed $gear
     * @param mixed $id
     *
     * @return bool
     */
    public function attachStack($gear, $id) {
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
            if (!$gear) {
                throw new \Exception('An invalid gear was selected.');
            }
            if ($gear->user_id != $user->id && !$user->hasPower('edit_inventories')) {
                throw new \Exception('You do not own this gear.');
            }
            if (!$character) {
                throw new \Exception('An invalid character was selected.');
            }
            if ($character->user_id !== $user->id && !$user->hasPower('edit_inventories')) {
                throw new \Exception('You do not own this character.');
            }
            if (isset($gear->gear->category->class_restriction)) {
                if (!$character->class_id || $character->class_id != $gear->gear->category->class_restriction) {
                    throw new \Exception('This character does not have the right class to hold this gear.');
                }
            }
            if ($character->gear()->where('gear_id', $gear->gear->id)->exists()) {
                throw new \Exception('This type of gear is already attached to the character selected.');
            }
            foreach ($character->gear as $cwea) {
                if ($cwea->gear->gear_category_id == $gear->gear->gear_category_id) {
                    throw new \Exception('This type of gear is already attached to the character selected.');
                }
            }

            $gear['character_id'] = $character->id;
            $gear['attached_at'] = Carbon::now();
            $gear->save();

            return $this->commitReturn(true);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }

    /**
     * detaches a gear stack.
     *
     * @param mixed $gear
     */
    public function detachStack($gear) {
        DB::beginTransaction();

        try {
            $user = Auth::user();
            if (!$user->hasAlias) {
                throw new \Exception('Your deviantART account must be verified before you can perform this action.');
            }
            if (!$gear) {
                throw new \Exception('An invalid gear was selected.');
            }
            if ($gear->user_id != $user->id && !$user->hasPower('edit_inventories')) {
                throw new \Exception('You do not own this gear.');
            }

            $gear['character_id'] = null;
            $gear['attached_at'] = null;
            $gear->save();

            return $this->commitReturn(true);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }

    /**
     * @param UserGear
     * @param mixed $gear
     * @param mixed $isStaff
     * @param mixed $child_id
     *
     * @return UserGear
     */
    public function upgradeGear($gear, $child_id, $isStaff = false) {
        DB::beginTransaction();

        try {
            $childGear = Gear::find($child_id);
            if (!$childGear) {
                throw new \Exception('Invalid gear selected.');
            }

            if ($childGear->parent_id != $gear->gear_id) {
                throw new \Exception('Invalid gear selected.');
            }

            if (!$isStaff) {
                if ($gear->user_id != Auth::user()->id && !$isStaff) {
                    throw new \Exception('You do not own this gear.');
                }

                $limitData = [
                    'character_ids' => $gear->character_id ? [$gear->character_id] : [],
                ];

                if (count(getLimits($childGear))) {
                    $limitService = new LimitManager;
                    if (!$limitService->checkLimits($childGear, false, $limitData, 'Gear Upgrade', 'Upgraded '.$gear->gear->displayName.' to '.$childGear->displayName)) {
                        foreach ($limitService->errors()->getMessages()['error'] as $error) {
                            flash($error)->error();
                        }

                        throw new \Exception('Failed to upgrade gear.');
                    }
                }
            }

            $gear->gear_id = $child_id;
            $gear->save();

            return $this->commitReturn(true);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }

    /**
     * Image -> gives unique image.
     *
     * @param mixed $gear
     * @param mixed $data
     */
    public function addImage($gear, $data) {
        DB::beginTransaction();

        try {
            $gear->has_image = 1;
            $gear->save();

            $image = null;
            if (isset($data['remove_image'])) {
                if ($gear && $gear->has_image && $data['remove_image']) {
                    $data['has_image'] = 0;
                    $this->deleteImage($gear->categoryImagePath, $gear->categoryImageFileName);
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

            $this->handleImage($image, $gear->imagePath, $gear->imageFileName);

            return $this->commitReturn(true);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }

    /**
     * Credits an gear to a user.
     *
     * @param User                  $sender
     * @param User                  $recipient
     * @param string                $type
     * @param array                 $data
     * @param \App\Models\Gear\Gear $gear
     * @param int                   $quantity
     *
     * @return bool
     */
    public function creditGear($sender, $recipient, $type, $data, $gear, $quantity) {
        DB::beginTransaction();

        try {
            for ($i = 0; $i < $quantity; $i++) {
                UserGear::create(['user_id' => $recipient->id, 'gear_id' => $gear->id, 'data' => json_encode($data)]);
            }
            if ($type && !$this->createLog($sender ? $sender->id : null, $recipient->id, null, $type, $data['data'], $gear->id, $quantity)) {
                throw new \Exception('Failed to create log.');
            }

            return $this->commitReturn(true);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }

    /**
     * Moves an gear stack from one user to another.
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

            if ($type && !$this->createLog($sender ? $sender->id : null, $recipient->id, $stack->id, $type, $data['data'], $stack->gear_id, 1)) {
                throw new \Exception('Failed to create log.');
            }

            return $this->commitReturn(true);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }

    /**
     * Debits an gear from a user.
     *
     * @param User                      $user
     * @param string                    $type
     * @param array                     $data
     * @param \App\Models\Gear\UserGear $stack
     *
     * @return bool
     */
    public function debitStack($user, $type, $data, $stack) {
        DB::beginTransaction();

        try {
            $stack->delete();

            if ($type && !$this->createLog($user ? $user->id : null, null, $stack->id, $type, $data['data'], $stack->gear_id, 1)) {
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
     * @param mixed  $gearId
     *
     * @return int
     */
    public function createLog($senderId, $recipientId, $stackId, $type, $data, $gearId, $quantity) {
        return DB::table('user_gears_log')->insert(
            [
                'sender_id'    => $senderId,
                'recipient_id' => $recipientId,
                'stack_id'     => $stackId,
                'log'          => $type.($data ? ' ('.$data.')' : ''),
                'log_type'     => $type,
                'data'         => $data, // this should be just a string
                'gear_id'      => $gearId,
                'quantity'     => $quantity,
                'created_at'   => Carbon::now(),
                'updated_at'   => Carbon::now(),
            ]
        );
    }
}
