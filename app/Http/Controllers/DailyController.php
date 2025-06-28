<?php

namespace App\Http\Controllers;

use App\Models\Daily\Daily;
use App\Models\Daily\DailyTimer;
use App\Services\DailyManager;
use App\Services\LimitManager;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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
        if ($daily->type == 'Advent' && !isset($daily->start_at)) {
            flash('Advent Daily is a type of daily that is a countdown to a specific date, therefore, a "Start At" date must be set.')->error();
            return redirect()->to('dailies');
        }

        $timer = Auth::user() ? DailyTimer::where('daily_id', $daily->id)->where('user_id', Auth::user()->id)->first() : null;

        return view('dailies.daily', [
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
     * @param mixed                     $id
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postRoll(Request $request, DailyManager $service, $id) {
        $daily = Daily::where('id', $id)->where('is_active', 1)->first();
        if (!$daily) {
            flash('Invalid '.__('dailies.daily').' selected.')->error();

            return redirect()->back();
        }

        if (count(getLimits($daily))) {
            if (!Auth::check()) {
                flash('You must be logged in to claim this daily.')->error();

                return redirect()->to('dailies');
            }

            $limitService = new LimitManager;
            if (!$limitService->checkLimits($daily)) {
                flash($limitService->errors()->getMessages()['error'][0])->error();

                return redirect()->to('dailies');
            }
        }

        $wheelSegment = null;
        if ($daily->type == 'Wheel') {
            $wheelSegment = random_int(1, $daily->wheel->segment_number);
        }

        if (!$rewards = $service->rollDaily($daily, Auth::user(), $wheelSegment)) {
            foreach ($service->errors()->getMessages()['error'] as $error) {
                flash($error)->error();
            }
        } else {
            flash('You have received: '.createRewardsString($rewards))->success();
        }

        if (!$request->ajax()) {
            return redirect()->back();
        }

        return $wheelSegment;
    }
}
