<?php

namespace App\Http\Controllers;

use App\Models\Daily\Daily;
use App\Models\Daily\DailyTimer;
use App\Services\DailyManager;
use Auth;
use Illuminate\Http\Request;

class DailyController extends Controller {
    /*
    |--------------------------------------------------------------------------
    | Daily Controller
    |--------------------------------------------------------------------------
    |
    | Handles viewing the Daily index, dailies and doing dailies.
    |
    */

    /**
     * Shows the Daily index.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getIndex() {
        return view('dailies.index', [
            'dailies' => Daily::where('is_active', 1)->orderBy('sort', 'DESC')->get(),
        ]);
    }

    /**
     * Shows a Daily.
     *
     * @param int $id
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getDaily($id, DailyManager $service) {
        $daily = Daily::where('id', $id)->where('is_active', 1)->first();
        if (!$daily) {
            abort(404);
        }
        $timer = (Auth::user()) ? DailyTimer::where('daily_id', $daily->id)->where('user_id', Auth::user()->id)->first() : null;

        return view('dailies.dailies', [
            'daily'    => $daily,
            'dailies'  => Daily::where('is_active', 1)->orderBy('sort', 'DESC')->get(),
            'timer'    => $timer,
            'cooldown' => $service->getDailyCooldown($daily, $timer),
        ]);
    }

    /**
     * Handles a daily roll.
     *
     * @param App\Services\DailyService $service
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postRoll(Request $request, DailyManager $service) {
        $request->validate(DailyTimer::$createRules);
        // Check that the daily exists and is open
        $daily = Daily::where('id', $request['daily_id'])->where('is_active', 1)->first();
        if (!$daily) {
            throw new \Exception('Invalid '.__('dailies.daily').' selected.');
        }

        if ($daily->type == 'Wheel') {
            $wheelSegment = random_int(1, $daily->wheel->segment_number);
            $rewards = $service->rollDaily($daily, Auth::user(), $wheelSegment);
        } else {
            $rewards = $service->rollDaily($daily, Auth::user());
        }

        if (!$rewards) {
            foreach ($service->errors()->getMessages()['error'] as $error) {
                flash($error)->error();
            }
        } else {
            $rolledRewards = 0;
            foreach ($rewards as $rewardList) {
                foreach ($rewardList as $reward) {
                    $rolledRewards += 1;
                    flash('You received '.$reward['quantity'].'x '.$reward['asset']->name.'!');
                }
            }
            if ($rolledRewards <= 0) {
                flash('You received nothing. Better luck next time!');
            }
        }

        if (!$request->ajax()) {
            return redirect()->back();
        } else {
            return $wheelSegment;
        }
    }
}
