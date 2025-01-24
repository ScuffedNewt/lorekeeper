<?php

namespace App\Services;

use App\Models\Currency\Currency;
use App\Models\Daily\Daily;
use App\Models\Daily\DailyTimer;
use App\Models\Item\Item;
use Carbon\Carbon;
use Datetime;
use DB;

class DailyManager extends Service {
    /*
    |--------------------------------------------------------------------------
    | Daily Manager
    |--------------------------------------------------------------------------
    |
    | Handles the rolling of dailies.
    |
    */

    /**********************************************************************************************

        DAILIES

     **********************************************************************************************/

    /**
     * Rolls an item/currency from the daily.
     *
     * @param \App\Models\User\User $user
     * @param mixed                 $daily
     * @param mixed|null            $wheelSegment
     *
     * @return App\Models\DailyTimer\DailyTimer|bool
     */
    public function rollDaily($daily, $user, $wheelSegment = null) {
        DB::beginTransaction();

        try {
            if (!Daily::where('id', $daily->id)->active()->exists()) {
                throw new \Exception('The daily is not active.');
            }
            if (!$this->canRoll($daily, $user)) {
                throw new \Exception('You have already received your reward.');
            }

            $dailyTimer = DailyTimer::where('daily_id', $daily->id)->where('user_id', $user->id)->first();
            if (!$dailyTimer) {
                $dailyTimer = DailyTimer::create([
                    'daily_id'  => $daily->id,
                    'user_id'   => $user->id,
                    'rolled_at' => Carbon::now(),
                    'step'      => 1,
                ]);
            } else {
                $dailyTimer->step = $this->getNextStep($daily, $dailyTimer);
                $dailyTimer->rolled_at = Carbon::now();
            }
            $dailyTimer->save();

            // Check and debit the fee in case the daily has a fee
            if ($daily->currency && $daily->fee > 0) {
                if (!(new CurrencyManager)->debitCurrency($user, null, 'Daily Fee', 'Paid fee for '.__('dailies.daily').' (<a href="'.$daily->viewUrl.'">#'.$daily->id.'</a>)', $daily->currency, $daily->fee)) {
                    throw new \Exception('You do not own enough currency to roll this daily.');
                }
            }

            if ($daily->type == 'Wheel') { // wheel actually always gets the step calculated by the wheel segment
                $dailyRewards = $daily->rewards()->where('step', $wheelSegment)->get();
            } else {
                $dailyRewards = $daily->rewards()->where('step', $dailyTimer->step)->get();
            }

            // if there is no reward, check if step 0 rewards (Default) are set and pick that instead
            if ($dailyRewards->count() <= 0) {
                $dailyRewards = $daily->rewards()->where('step', 0)->get();
            }

            $assets = createAssetsArray(false);
            foreach ($dailyRewards as $reward) {
                addAsset($assets, $reward->reward, $reward->quantity);
            }

            // Distribute user rewards
            $logType = ucwords(__('dailies.daily')).' Rewards';
            $dailyData = [
                'data' => 'Received rewards for '.__('dailies.daily').' (<a href="'.$daily->viewUrl.'">#'.$daily->id.'</a>)',
            ];

            $rewards = fillUserAssets($assets, null, $user, $logType, $dailyData);
            if (!$rewards) {
                throw new \Exception('Failed to distribute rewards to user.');
            }

            return $this->commitReturn($rewards);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }

    /**
     * Checks if user can roll the daily.
     *
     * @param \App\Models\User\User $user
     * @param mixed                 $daily
     *
     * @return bool
     */
    public function canRoll($daily, $user) {
        if (!$daily) {
            return false;
        }
        if (!Daily::where('id', $daily->id)->active()->exists()) {
            return false;
        }
        $dailyTimer = DailyTimer::where('daily_id', $daily->id)->where('user_id', $user->id)->first();

        if ($dailyTimer) {
            // if the daily does not loop, we stop users once they collected the max step.
            if (!$daily->is_loop && $dailyTimer->step >= $daily->maxStep) {
                return false;
            }

            // if a timer exists we cannot roll again if the time is right
            if ($dailyTimer->rolled_at >= $daily->dailyTimeframeDate) {
                return false;
            }
        }

        return true;
    }

    public function getDailyCooldown($daily, $timer) {
        // If there is no timer, the cooldown is null
        if (!$timer) {
            return null;
        }
        // If the timer is up/we are good, cooldown is also null
        if ($timer->rolled_at < $daily->dailyTimeframeDate) {
            return null;
        }

        // return next date
        return Carbon::createFromFormat('Y-m-d H:i:s', $daily->nextDate);
    }

    private function getNextStep($daily, $dailyTimer) {
        $step = $dailyTimer->step;
        $maxStep = $daily->maxStep;

        //if streak daily, check if a day was missed and if so, set dailytimer step to 1
        if ($daily->type == 'Wheel') {
            return 0;
        }
        if ($daily->is_streak && !$this->isActiveStreak($daily, $dailyTimer)) {
            return 1;
        }
        if ($step == $maxStep) {
            return 1;
        }
        if ($step < $maxStep) {
            return $step += 1;
        }
        if ($step > $maxStep) {
            throw new \Exception('There was an issue with assigning the next daily step.');
        }
    }

    private function isActiveStreak($daily, $dailyTimer) {
        $date1 = new Datetime($daily->dailyTimeframeDate);
        $date2 = new Datetime($dailyTimer->rolled_at);
        $interval = $date1->diff($date2);
        switch ($daily->daily_timeframe) {
            case 'yearly':
                return $interval->y < 1;
            case 'monthly':
                return $interval->m < 1;
            case 'weekly':
                return $interval->d < 7;
            case 'daily':
                return $interval->d < 1;
            default:
                return false;
        }
    }
}
