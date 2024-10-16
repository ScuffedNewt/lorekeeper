<?php

namespace App\Services\Stat;

use App\Facades\Notifications;
use App\Models\Character\Character;
use App\Models\Stat\Stat;
use App\Models\User\User;
use App\Models\User\UserLevel;
use App\Services\Service;
use Auth;
use Carbon\Carbon;
use DB;

class StatManager extends Service {
    /**
     * Levels up a character's stat. User's do not have stats so this only works for characters.
     *
     * @param mixed $character
     * @param mixed $stat      App\Models\Stat\Stat
     * @param bool  $isStaff
     */
    public function levelCharacterStat($character, $stat, $isStaff = false) {
        DB::beginTransaction();

        try {
            // registering previous
            $character_stat = $character->stats()->where('stat_id', $stat->id)->first();
            $previous_level = $character_stat->stat_level;

            // incrementing
            $character_stat->stat_level += 1;
            $character_stat->save();

            if ($stat->multiplier || $stat->increment) {
                // we want to update the current_count too
                if (!$character_stat->current_count) {
                    $character_stat->current_count = $character_stat->count;
                }

                // First if there's an increment, add that
                // This is so that the multiplier affects the new step total
                // E.G if the current is 10 and step is 5, we do 15 * multiplier
                // This can be changed if desired but generally I think this is fine

                if ($stat->increment) {
                    $character_stat->count += $stat->increment;
                    $character_stat->current_count += $stat->increment;
                    $character_stat->save();
                }
                if ($stat->multiplier) {
                    $total = $stat->count * $stat->multiplier;
                    $character_stat->count = $total;
                    $character_stat->current_count = $total;
                    $character_stat->save();
                }
            } else {
                // if there's no multiplier or step, just add 1
                $character_stat->count += 1;
                $character_stat->current_count += 1;
                $character_stat->save();
            }

            if (!$isStaff) {
                if ($character->level->current_points + $character->user->level->current_points < 1) {
                    throw new \Exception('Not enough points to level up.');
                }

                // consume character points first
                if ($character->level->current_points) {
                    $character->level->current_points -= 1;
                    $character->level->save();
                } else {
                    // if no character points, consume user points
                    $character->user->level->current_points -= 1;
                    $character->user->level->save();
                }

                $type = 'Stat Level Up';
                $data = 'Point used in stat level up.';
                if (!$this->createTransferLog($character->id, 'Character', null, null, $type, $data, -1)) {
                    throw new \Exception('Error creating log.');
                }
                if (!$this->createLevelLog($character->id, 'Character', $stat->id, $previous_level, $character_stat->stat_level)) {
                    throw new \Exception('Error creating log.');
                }
            }

            return $this->commitReturn(true);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }

    /**
     * sets the stat BASE VALUE directly.
     *
     * @param mixed $stat
     * @param mixed $character
     * @param mixed $quantity
     */
    public function editCharacterStatBaseCount($stat, $character, $quantity) {
        DB::beginTransaction();

        try {
            $sender = Auth::user();
            if (!$sender->isStaff) {
                throw new \Exception('You are not staff.');
            }

            $stat = $character->stats()->where('stat_id', $stat->id)->first();

            $stat->count = $quantity;
            $stat->save();

            $type = 'Staff Edit';
            $data = 'Edited Base Stat Value by Staff';

            if (!$this->createCountLog($sender->id, $sender->logtype, $character, $type, $data, $quantity, $stat->id)) {
                throw new \Exception('Error creating log.');
            }

            return $this->commitReturn(true);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }

    /**
     * Edit the current stat amount. Edits directly unless $set is false, then it increments / decrements.
     * Staff Only function, unless override is true.
     *
     * @param mixed $stat
     * @param mixed $character
     * @param mixed $quantity
     * @param bool  $override
     * @param mixed $set
     */
    public function editCharacterStatCurrentCount($stat, $character, $quantity, $set = true, $override = false) {
        DB::beginTransaction();

        try {
            $sender = Auth::user();
            if (!$sender->isStaff && !$override) {
                throw new \Exception('You are not staff.');
            }

            $stat = $character->stats()->where('stat_id', $stat->id)->first();
            if (!$stat->current_count) {
                $stat->current_count = $stat->count;
                $stat->save();
            }

            if ($set) {
                // check if there are any weapons / gear granting extra to this stat
                $count = $character->totalStatCount($stat->stat->id);
                // $count += $character->bonusStatCount($stat->id); TODO check bonusStatCount func
                $stat->current_count = $quantity > $count ? $count : $quantity;
            } else {
                $stat->current_count += $quantity;
            }
            $stat->save();

            if ($override) {
                // if different logs are needed
                $type = $override['type'];
                $data = $override['data'];
            } else {
                $type = 'Staff Edit';
                $data = 'Edited Current Count by Staff';
            }

            if (!$this->createCountLog($sender->id, $sender->logtype, $character, $type, $data, $quantity, $stat->id)) {
                throw new \Exception('Error creating log.');
            }

            return $this->commitReturn(true);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }

    /**
     * Grants / transfers Stat points users or characters.
     *
     * @param mixed $data
     * @param mixed $staff
     */
    public function grantStats($data, $staff) {
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

                foreach ($data['stat_ids'] as $key=>$stat_id) {
                    $stat = $stat_id == 'none' ? $stat_id : Stat::find($stat_id);
                    if (!$this->creditStat($staff, $user, 'Staff Grant', $data['data'], $stat, $data['quantity'][$key], true)) {
                        throw new \Exception('Failed to credit points to '.$user->name.'.');
                    }
                }

                Notifications::create('STAT_GRANT', $user, [
                    'sender_url'  => $staff->url,
                    'sender_name' => $staff->name,
                    'stat_url'    => '/stats',
                ]);
            }

            foreach ($characters as $id) {
                $character = Character::find(substr($id, 10));
                if (!$character) {
                    throw new \Exception('An invalid character was selected.');
                }

                foreach ($data['stat_ids'] as $key=>$stat_id) {
                    $stat = $stat_id == 'none' ? $stat_id : Stat::find($stat_id);
                    if (!$this->creditStat($staff, $character, 'Staff Grant', $data['data'], $stat, $data['quantity'][$key], true)) {
                        throw new \Exception('Failed to credit points to '.$character->fullName.'.');
                    }
                }
                Notifications::create('STAT_GRANT', $character->user, [
                    'sender_url'  => $staff->url,
                    'sender_name' => $staff->name,
                    'stat_url'    => '/character/'.$character->slug.'/stats',
                ]);
            }

            return $this->commitReturn(true);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }

    /**
     * Grants / transfers Stat points (to level up) to one user or character.
     *
     * @param mixed $sender
     * @param mixed $recipient
     * @param mixed $type
     * @param mixed $data
     * @param mixed $stat
     * @param mixed $quantity
     * @param mixed $isStaff
     */
    public function creditStat($sender, $recipient, $type, $data, $stat, $quantity, $isStaff = false) {
        DB::beginTransaction();

        try {
            // for user
            if ($recipient->logType == 'User') {
                if (!$recipient->level) {
                    $recipient->level()->create([
                        'user_id' => $recipient->id,
                    ]);
                }

                // we only grant general points to users
                if ($stat == 'none') {
                    // if no data is passed aka notes
                    if (!$data && $isStaff) {
                        $data = 'Staff Grant of '.$quantity.' general stat points';
                    }

                    $recipient->level->current_points += $quantity;
                    $recipient->level->save();
                }
            }
            // for character
            else {
                if (!$recipient->level) {
                    $recipient->level()->create([
                        'character_id' => $recipient->id,
                    ]);
                }

                // propagate stats
                $recipient->propagateStats();
                if ($stat == 'none') {
                    // if no data is passed aka notes
                    if (!$data && $isStaff) {
                        $data = 'Staff Grant of '.$quantity.' general stat points';
                    }

                    $recipient->level->current_points += $quantity;
                    $recipient->level->save();
                } else {
                    // if we are granting a specific stat
                    $character_stat = $recipient->stats()->where('stat_id', $stat->id)->first();

                    if (!$character_stat) {
                        throw new \Exception('The stat '.$stat->name.' does not exist for the character '.$recipient->fullName.'. Check if the stat is allowed on this character.');
                    }

                    // we can't just increment the count of the stat, we have to level it up
                    $this->levelCharacterStat($recipient, $character_stat, true);

                    if (!$data) {
                        $data = 'Staff granted stat level up on '.$stat->name.' to  lvl'.$character_stat->stat_level + 1 .'.';
                    }

                    if (!$this->createLevelLog($recipient->id, $stat->id, 'Character', $character_stat->stat_level, $character_stat->stat_level + 1)) {
                        throw new \Exception('Error creating log.');
                    }
                }
            }
            if ($type && !$this->createTransferLog($sender ? $sender->id : null, $sender ? $sender->logType : null, $recipient ? $recipient->id : null, $recipient ? $recipient->logType : null, $type, $data, $quantity)) {
                throw new \Exception('Failed to create log.');
            }

            return $this->commitReturn(true);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }

    /**
     * debit Stat points.
     *
     * @param mixed $sender
     * @param mixed $recipient
     * @param mixed $type
     * @param mixed $data
     * @param mixed $quantity
     */
    public function debitStat($sender, $recipient, $type, $data, $quantity) {
        DB::beginTransaction();

        try {
            // for user
            if ($sender->logType == 'User') {
                $sender_stack = UserLevel::where('user_id', '=', $sender->id)->first();

                if (!$sender_stack) {
                    $sender_stack = UserLevel::create(['user_id' => $sender->id]);
                }
                if ($sender_stack->current_points < $quantity) {
                    throw new \Exception('Not enough points to debit.');
                }
                $sender_stack->current_points -= $quantity;
                $sender_stack->save();
            }
            // for character
            else {
                $sender_stack = CharaLevels::where('character_id', $sender->id)->first();

                if (!$sender_stack) {
                    $sender_stack = CharaLevels::create(['character_id' => $sender->id]);
                }
                if ($sender_stack->current_points < $quantity) {
                    throw new \Exception('Not enough points to debit.');
                }
                $sender_stack->current_points -= $quantity;
                $sender_stack->save();
            }
            if ($type && !$this->createTransferLog($sender ? $sender->id : null, $sender ? $sender->logType : null, $recipient ? $recipient->id : null, $recipient ? $recipient->logType : null, $type, $data, $quantity * -1)) {
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
    public function createTransferLog($senderId, $senderType, $recipientId, $recipientType, $type, $data, $quantity) {
        return DB::table('stat_transfer_log')->insert(
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

    /**
     * Creates a log.
     *
     * @param mixed $recipientId
     * @param mixed $stat
     * @param mixed $recipientType
     * @param mixed $previous
     * @param mixed $new
     */
    public function createLevelLog($recipientId, $recipientType, $stat, $previous, $new) {
        return DB::table('stat_log')->insert(
            [
                'recipient_id'   => $recipientId,
                'stat_id'        => $stat,
                'leveller_type'  => $recipientType,
                'previous_level' => $previous,
                'new_level'      => $new,
                'created_at'     => Carbon::now(),
                'updated_at'     => Carbon::now(),
            ]
        );
    }

    /**
     * Creates a log.
     *
     * @param mixed      $senderId
     * @param mixed      $senderType
     * @param mixed      $character
     * @param mixed      $type
     * @param mixed      $data
     * @param mixed      $quantity
     * @param mixed|null $stat_id
     */
    public function createCountLog($senderId, $senderType, $character, $type, $data, $quantity, $stat_id = null) {
        return DB::table('count_log')->insert(
            [
                'sender_id'    => $senderId ?? null,
                'sender_type'  => $senderType,
                'character_id' => $character->id,
                'log'          => $type.($data ? ' ('.$data.')' : ''),
                'log_type'     => $type,
                'data'         => $data,
                'quantity'     => $quantity,
                'created_at'   => Carbon::now(),
                'updated_at'   => Carbon::now(),
                'stat_id'      => $stat_id ?? null,
            ]
        );
    }
}
