<?php

namespace App\Services\Stat;

use App\Facades\Notifications;
use App\Models\Character\Character;
use App\Models\Character\CharacterExperience;
use App\Models\Stat\Experience;
use App\Models\User\User;
use App\Models\User\UserExperience;
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

            if (!isset($data['experience_id'])) {
                throw new \Exception('An invalid experience was selected.');
            }

            $experience = Experience::find($data['experience_id']);
            if (!$experience) {
                throw new \Exception('An invalid experience was selected.');
            }

            foreach ($usernames as $id) {
                $user = User::find(substr($id, 5));
                if (!$user) {
                    throw new \Exception('An invalid user was selected.');
                }

                if ($this->creditExperience($staff, $user, 'Staff Grant', $data['data'], $experience, $data['quantity'])) {
                    Notifications::create('EXP_GRANT', $user, [
                        'quantity'         => $data['quantity'],
                        'experience_name'  => $experience->name,
                        'sender_url'       => $staff->url,
                        'sender_name'      => $staff->name,
                        'stat_url'         => url('user-stats'),
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

                if ($this->creditExperience($staff, $character, 'Staff Grant', $data['data'], $experience, $data['quantity'])) {
                    Notifications::create('EXP_GRANT', $character->user, [
                        'quantity'         => $data['quantity'],
                        'experience_name'  => $experience->name,
                        'sender_url'       => $staff->url,
                        'sender_name'      => $staff->name,
                        'stat_url'         => url('character/'.$character->slug.'/stats'),
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
     * @param mixed $data
     * @param mixed $quantity
     * @param mixed $logType
     * @param mixed $experience
     */
    public function creditExperience($sender, $recipient, $logType, $data, $experience, $quantity) {
        DB::beginTransaction();

        try {
            $recipient_stack = null;
            if ($recipient->logType == 'User') {
                $recipient_stack = UserExperience::where('user_id', $recipient->id)->where('experience_id', $experience->id)->first();
                if (!$recipient_stack) {
                    $recipient_stack = UserExperience::create(['user_id' => $recipient->id, 'experience_id' => $experience->id]);
                }
            } else {
                $recipient_stack = CharacterExperience::where('character_id', $recipient->id)->where('experience_id', $experience->id)->first();

                if (!$recipient_stack) {
                    $recipient_stack = CharacterExperience::create(['character_id' => $recipient->id, 'experience_id' => $experience->id]);
                }
            }

            if (!$recipient_stack) {
                throw new \Exception('Failed to create experience stack.');
            }

            $recipient_stack->quantity += $quantity;
            $recipient_stack->save();

            if ($logType && !$this->createLog($sender ? $sender->id : null, $sender ? $sender->logType : null, $recipient ? $recipient->id : null, $recipient ? $recipient->logType : null, $logType, $data, $quantity)) {
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
     * @param mixed $data
     * @param mixed $quantity
     * @param mixed $logType
     * @param mixed $experience
     */
    public function debitExp($owner, $logType, $data, $experience, $quantity) {
        DB::beginTransaction();

        try {
            $experience_stack = null;
            if ($owner->logType == 'User') {
                $experience_stack = UserExperience::where('user_id', $owner->id)->where('experience_id', $experience->id)->first();
            } else {
                $experience_stack = CharacterExperience::where('character_id', $owner->id)->where('experience_id', $experience->id)->first();
            }

            if (!$experience_stack) {
                throw new \Exception('Experience stack not found.');
            }

            if ($experience_stack->quantity < $quantity) {
                throw new \Exception('Owner does not have enough experience to debit.');
            }

            $experience_stack->quantity -= $quantity;
            $experience_stack->save();

            if ($logType && !$this->createLog($owner ? $owner->id : null, $owner ? $owner->logType : null, null, null, $logType, $data, -$quantity)) {
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
        return DB::table('experience_logs')->insert(
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
