<?php

namespace App\Services;

use App\Models\Currency\Currency;
use App\Models\Item\Item;
use App\Models\Raffle\Raffle;
use App\Models\Raffle\RaffleLog;
use App\Models\Raffle\RaffleTicket;
use App\Models\User\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class RaffleManager extends Service {
    /*
    |--------------------------------------------------------------------------
    | Raffle Manager
    |--------------------------------------------------------------------------
    |
    | Handles creation and modification of raffle ticket data.
    |
    */

    /**
     * Adds tickets to a raffle.
     *
     * @param \App\Models\Raffle\Raffle $raffle
     * @param array                     $data
     *
     * @return int
     */
    public function addTickets($raffle, $data) {
        $count = 0;
        foreach ($data['user_id'] as $key=> $id) {
            if ($user = User::where('id', $id)->first()) {
                if ($this->addTicket($user, $raffle, $data['ticket_count'][$key])) {
                    $count += $data['ticket_count'][$key];
                }
            } else {
                if ($this->addTicket($data['alias'][$key], $raffle, $data['ticket_count'][$key])) {
                    $count += $data['ticket_count'][$key];
                }
            }
        }

        return $count;
    }

    /**
     * Adds one or more tickets to a single user for a raffle.
     *
     * @param \App\Models\User\User     $user
     * @param \App\Models\Raffle\Raffle $raffle
     * @param int                       $count
     *
     * @return int
     */
    public function addTicket($user, $raffle, $count = 1) {
        if (!$user) {
            return 0;
        } elseif (!$raffle) {
            return 0;
        } elseif ($count == 0) {
            return 0;
        } elseif ($raffle->rolled_at != null) {
            return 0;
        } elseif ($raffle->ticket_cap > 0 && ((is_string($user) ? $raffle->tickets()->where('alias', $user)->count() : $raffle->tickets()->where('user_id', $user->id)->count()) > $raffle->ticket_cap || (is_string($user) ? $raffle->tickets()->where('alias', $user)->count() : $raffle->tickets()->where('user_id', $user->id)->count()) + $count > $raffle->ticket_cap)) {
            return 0;
        } else {
            DB::beginTransaction();
            if ($raffle->is_fto) {
                if (!$user->settings->is_fto && $user->characters->count() > 0) {
                    throw new \Exception('One or more users is not a FTO or Non-Owner and cannot enter this raffle!');
                }
            }
            $data = ['raffle_id' => $raffle->id, 'created_at' => Carbon::now()] + (is_string($user) ? ['alias' => $user] : ['user_id' => $user->id]);
            if (is_object($user)) {
                $this->grantRewards($raffle, $user);
            }
            for ($i = 0; $i < $count; $i++) {
                RaffleTicket::create($data);
            }
            DB::commit();

            return 1;
        }

        return 0;
    }

    // enters self into raffle
    public function selfEnter($raffle, $user) {
        DB::beginTransaction();

        try {
            if (!$user || !$raffle) {
                throw new \Exception('An error occured.');
            }
            if (!$raffle->allow_entry) {
                throw new \Exception('You cannot enter yourself into this raffle!');
            }
            if (RaffleTicket::where('user_id', $user->id)->where('raffle_id', $raffle->id)->exists()) {
                throw new \Exception('You may only enter once!');
            }
            if ($raffle->is_fto) {
                if (!$user->settings->is_fto && $user->characters->count() > 0) {
                    throw new \Exception('You must be a FTO or Non-Owner to enter this raffle!');
                }
            }
            if ($raffle->rolled_at != null) {
                throw new \Exception('This raffle has been rolled.');
            }
            if ($raffle->end_at && $raffle->end_at->isPast()) {
                throw new \Exception('This raffle has ended.');
            }

            RaffleTicket::create([
                'user_id'    => $user->id,
                'raffle_id'  => $raffle->id,
                'created_at' => Carbon::now(),
            ]);

            $this->grantRewards($raffle, $user);

            return $this->commitReturn(true);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }

    /**
     * Removes a single ticket.
     *
     * @param \App\Models\Raffle\RaffleTicket $ticket
     *
     * @return bool
     */
    public function removeTicket($ticket) {
        if (!$ticket) {
            return null;
        } else {
            $ticket->delete();

            return true;
        }

        return false;
    }

    /**
     * Rolls a raffle group consecutively.
     * If the $updateGroup flag is true, winners will be removed
     * from other raffles in the group.
     *
     * @param \App\Models\Raffle\RaffleGroup $raffleGroup
     * @param bool                           $updateGroup
     *
     * @return bool
     */
    public function rollRaffleGroup($raffleGroup, $updateGroup = true) {
        if (!$raffleGroup) {
            return null;
        }
        DB::beginTransaction();
        foreach ($raffleGroup->raffles()->orderBy('order')->get() as $raffle) {
            if (!$this->rollRaffle($raffle, $updateGroup)) {
                DB::rollback();

                return false;
            }
        }
        $raffleGroup->is_active = 2;
        $raffleGroup->save();
        DB::commit();

        return true;
    }

    /**
     * Rolls a single raffle and marks it as completed.
     * If the $updateGroup flag is true, winners will be removed
     * from other raffles in the group.
     *
     * @param \App\Models\Raffle\Raffle $raffle
     * @param bool                      $updateGroup
     *
     * @return bool
     */
    public function rollRaffle($raffle, $updateGroup = false) {
        if (!$raffle) {
            return null;
        }
        DB::beginTransaction();
        // roll winners
        if ($winners = $this->rollWinners($raffle)) {
            // mark raffle as finished
            $raffle->is_active = 2;
            $raffle->rolled_at = Carbon::now();
            $raffle->save();

            // updates the raffle group if necessary
            if ($updateGroup && !$this->afterRoll($winners, $raffle->group, $raffle)) {
                DB::rollback();

                return false;
            }
            DB::commit();

            return true;
        }
        DB::rollback();

        return false;
    }

    /**
     * Rerolls a raffle winner.
     *
     * @param mixed $ticket
     * @param mixed $reason
     * @param mixed $user
     */
    public function rerollWinner($ticket, $reason, $user) {
        DB::beginTransaction();

        try {
            if (!$reason) {
                throw new \Exception('Please provide a reason for rerolling.');
            }
            if ($ticket->user) {
                $tickets = RaffleTicket::where('raffle_id', $ticket->raffle_id)->where('user_id', $ticket->user->id)->get();
                $rerollTickets = RaffleTicket::where('raffle_id', $ticket->raffle_id)->where(function ($query) use ($ticket) {
                    $query->where('user_id', '!=', $ticket->user->id)->orWhereNotNull('alias');
                })->get();
            } else {
                $tickets = RaffleTicket::where('raffle_id', $ticket->raffle_id)->where('alias', $ticket->alias)->get();
                $rerollTickets = RaffleTicket::where('raffle_id', $ticket->raffle_id)->where(function ($query) use ($ticket) {
                    $query->where('alias', '!=', $ticket->alias)->orWhereNotNull('user_id');
                })->get();
            }
            if ($rerollTickets->count() < 1) {
                throw new \Exception('No tickets to reroll.');
            }
            $n = $ticket->position;

            foreach ($tickets as $t) {
                $t->position = null;
                $t->reroll = 0;
                $t->save();
            }

            $num = mt_rand(0, count($rerollTickets) - 1);
            $winner = $rerollTickets[$num];

            $winner->position = $n;
            $winner->reroll = 1;
            $winner->save();

            if (!$this->logReroll($ticket, $reason, $user)) {
                throw new \Exception('Failed to log reroll.');
            }

            return $this->commitReturn(true);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }

    /**
     * Grants rewards to a user for a raffle.
     *
     * @param mixed $raffle
     * @param mixed $user
     */
    private function grantRewards($raffle, $user) {
        if ($raffle->rewards->count() > 0) {
            // check user hasn't already received rewards
            if ($raffle->logs()->where('user_id', $user->id)->where('type', 'Reward')->exists()) {
                flash('This user ('.$user->name.') has already received rewards for entering.')->info();

                return;
            }
            // Get the updated set of rewards
            $rewards = $this->processRewards($raffle->rewards, false, true);

            // Logging data
            $logType = 'Raffle Entry Rewards';
            $data = [
                'data' => 'Received rewards for entering the raffle '.$raffle->name.' (<a href="'.$raffle->url.'">#'.$raffle->id.'</a>)',
            ];

            // Distribute user rewards
            if (!$rewards = fillUserAssets($rewards, null, $user, $logType, $data)) {
                throw new \Exception('Failed to distribute rewards to user.');
            }

            // Log the rewards
            RaffleLog::create([
                'user_id'    => $user->id,
                'raffle_id'  => $raffle->id,
                'type'       => 'Reward',
                'reason'     => 'Entered Raffle',
                'created_at' => Carbon::now(),
            ]);
        }
    }

    /**
     * Processes reward data into a format that can be used for distribution.
     *
     * @param mixed $rewards
     *
     * @return array
     */
    private function processRewards($rewards) {
        $assets = createAssetsArray(false);
        // Process the additional rewards
        foreach ($rewards as $reward) {
            $asset = null;
            switch ($reward->rewardable_type) {
                case 'Item':
                    $asset = Item::find($reward->rewardable_id);
                    break;
                case 'Currency':
                    $asset = Currency::find($reward->rewardable_id);
                    if (!$asset->is_user_owned) {
                        throw new \Exception('Invalid currency selected.');
                    }
                    break;
            }
            if (!$asset) {
                continue;
            }
            addAsset($assets, $asset, $reward->quantity);
        }

        return $assets;
    }

    /**
     * Rolls the winners of a raffle.
     *
     * @param \App\Models\Raffle\Raffle $raffle
     *
     * @return array
     */
    private function rollWinners($raffle) {
        $ticketPool = $raffle->tickets;
        $ticketCount = $ticketPool->count();
        $winners = ['ids' => [], 'aliases' => []];
        $used = [];
        for ($i = 0; $i < $raffle->winner_count; $i++) {
            if ($ticketCount == 0) {
                break;
            }

            $num = mt_rand(0, $ticketCount - 1);
            $winner = $ticketPool[$num];

            if ($raffle->unordered && $raffle->tickets->count() < $raffle->winner_count) {
                // save ticket position as random number between 1 and winner_count
                $n = mt_rand(1, $raffle->winner_count);
                while (in_array($n, $used)) {
                    $n = mt_rand(1, $raffle->winner_count);
                }
                $winner->update(['position' => $n]);
                $used[] = $n;
            } else {
                // save ticket position as ($i + 1)
                $winner->update(['position' => $i + 1]);
            }

            // save the winning ticket's user id
            if (isset($winner->user_id)) {
                $winners['ids'][] = $winner->user_id;
            } else {
                $winners['aliases'][] = $winner->alias;
            }

            // remove ticket from the ticket pool after pulled
            $ticketPool->forget($num);
            $ticketPool = $ticketPool->values();

            $ticketCount--;

            // remove tickets for the same user...I'm unsure how this is going to hold up with 3000 tickets,
            foreach ($ticketPool as $key=> $ticket) {
                if (($ticket->user_id != null && $ticket->user_id == $winner->user_id) || ($ticket->user_id == null && $ticket->alias == $winner->alias)) {
                    $ticketPool->forget($key);
                }
            }
            $ticketPool = $ticketPool->values();
            $ticketCount = $ticketPool->count();
        }

        return $winners;
    }

    /**
     * Rolls the winners of a raffle.
     *
     * @param array                          $winners
     * @param \App\Models\Raffle\RaffleGroup $raffleGroup
     * @param \App\Models\Raffle\Raffle      $raffle
     *
     * @return bool
     */
    private function afterRoll($winners, $raffleGroup, $raffle) {
        // remove any tickets from winners in raffles in the group that aren't completed
        $raffles = $raffleGroup->raffles()->where('is_active', '!=', 2)->where('id', '!=', $raffle->id)->get();
        foreach ($raffles as $r) {
            $r->tickets()->where(function ($query) use ($winners) {
                $query->whereIn('user_id', $winners['ids'])->orWhereIn('alias', $winners['aliases']);
            })->delete();
        }

        return true;
    }

    private function logReroll($ticket, $reason, $user) {
        DB::beginTransaction();

        try {
            $log = RaffleLog::create([
                'raffle_id' => $ticket->raffle_id,
                'user_id'   => $user->id,
                'reason'    => 'Reroll: '.$reason,
                'ticket_id' => $ticket->id,
            ]);

            return $this->commitReturn(true);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }
}
