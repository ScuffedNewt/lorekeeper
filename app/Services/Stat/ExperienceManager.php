<?php

namespace App\Services\Stat;

use App\Facades\Notifications;
use App\Models\Character\Character;
use App\Models\Character\CharacterLevel;
use App\Models\User\User;
use App\Models\User\UserLevel;
use App\Services\Service;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ExperienceManager extends Service {
    /**
     * Grants EXP to multiple users or characters.
     *
     * @param array $data
     * @param User  $staff
     *
     * @return bool
     */
    public function grantExp($data, $staff) {
        DB::beginTransaction();

        try {
            $usernames = array_filter($data['names'], function ($name) {
                return substr($name, 0, 5) == 'user-';
            });
            $characters = array_filter($data['names'], function ($name) {
                return substr($name, 0, 10) == 'character-';
            });

            foreach ($usernames as $id) {
                $user = User::find(substr($id, 5));
                if (!$user) {
                    throw new \Exception('An invalid user was selected.');
                }

                if ($this->creditExp($staff, $user, 'Staff Grant', $data['data'], $data['quantity'])) {
                    Notifications::create('EXP_GRANT', $user, [
                        'quantity'         => $data['quantity'],
                        'sender_url'       => $staff->url,
                        'sender_name'      => $staff->name,
                        'stat_url'         => url('/user-stats'),
                    ]);
                } else {
                    throw new \Exception('Failed to credit exp to '.$user->name.'.');
                }
            }

            foreach ($characters as $id) {
                $character = Character::find(substr($id, 10));
                if (!$character) {
                    throw new \Exception('An invalid character was selected.');
                }

                if ($this->creditExp($staff, $character, 'Staff Grant', $data['data'], $data['quantity'], true)) {
                    Notifications::create('EXP_GRANT', $character->user, [
                        'quantity'         => $data['quantity'],
                        'sender_url'       => $staff->url,
                        'sender_name'      => $staff->name,
                        'stat_url'         => url('/character/'.$character->slug.'/stats'),
                    ]);
                } else {
                    throw new \Exception('Failed to credit exp to '.$character->fullName.'.');
                }
            }

            return $this->commitReturn(true);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }

    /**
     * Grants EXP to one user or character.
     *
     * @param mixed $sender
     * @param mixed $recipient
     * @param mixed $type
     * @param mixed $data
     * @param mixed $quantity
     * @param mixed $grant_to_character
     */
    public function creditExp($sender, $recipient, $type, $data, $quantity, $grant_to_character = false) {
        DB::beginTransaction();

        try {
            if ($grant_to_character) {
                $recipient = $recipient->level->character;
            }
            // for user
            if ($recipient->logType == 'User') {
                $recipient_stack = UserLevel::where('user_id', '=', $recipient->id)->first();

                if (!$recipient_stack) {
                    $recipient_stack = UserLevel::create(['user_id' => $recipient->id]);
                }
                $recipient_stack->current_exp += $quantity;
                $recipient_stack->save();
            }
            // for character
            else {
                $recipient_stack = CharacterLevel::where('character_id', $recipient->id)->first();

                if (!$recipient_stack) {
                    $recipient_stack = CharacterLevel::create(['character_id' => $recipient->id]);
                }
                $recipient_stack->current_exp += $quantity;
                $recipient_stack->save();
            }
            if ($type && !$this->createLog($sender ? $sender->id : null, $sender ? $sender->logType : null, $recipient ? $recipient->id : null, $recipient ? $recipient->logType : null, $type, $data, $quantity)) {
                throw new \Exception('Failed to create log.');
            }

            return $this->commitReturn(true);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }

    /**
     * Debits exp from a user or character.
     *
     * @param mixed $owner
     * @param mixed $type
     * @param mixed $data
     * @param mixed $level
     * @param mixed $quantity
     */
    public function debitExp($owner, $type, $data, $level, $quantity) {
        DB::beginTransaction();

        try {
            $level->current_exp -= $quantity;
            $level->save();

            if ($type && !$this->createLog($owner ? $owner->id : null, $owner ? $owner->logType : null, null, null, $type, $data, -$quantity)) {
                throw new \Exception('Failed to create log.');
            }

            return $this->commitReturn(true);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }

    /**
     * Creates a log.
     *
     * @param mixed $senderId
     * @param mixed $senderType
     * @param mixed $recipientId
     * @param mixed $recipientType
     * @param mixed $type
     * @param mixed $data
     * @param mixed $quantity
     */
    public function createLog($senderId, $senderType, $recipientId, $recipientType, $type, $data, $quantity) {
        return DB::table('exp_log')->insert(
            [
                'sender_id'      => $senderId,
                'sender_type'    => $senderType,
                'recipient_id'   => $recipientId,
                'recipient_type' => $recipientType,
                'log'            => $type.($data ? ' ('.$data.')' : ''),
                'log_type'       => $type,
                'data'           => $data,
                'quantity'       => $quantity,
                'created_at'     => Carbon::now(),
                'updated_at'     => Carbon::now(),
            ]
        );
    }
}
