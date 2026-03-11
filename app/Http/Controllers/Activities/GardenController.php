<?php

namespace App\Http\Controllers\Activities;

use App\Models\Garden\Garden;
use App\Models\Garden\GardenPlot;
use App\Models\Garden\UserGardenPlot;
use App\Models\User\User;
use App\Services\Garden\GardenManager;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;

class GardenController extends Controller {
    /**
     * Shows the garden index page.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getIndex() {
        $visibleGardens = Garden::visible(Auth::user() ?? null)->get();
        $isGardenUnlocked = $visibleGardens->mapWithKeys(function ($garden) {
            return [$garden->id => hasUnlockedLimits(Auth::user(), $garden)];
        });
        $groups = $visibleGardens->groupBy(function ($garden) use ($isGardenUnlocked) {
            return $isGardenUnlocked[$garden->id] ? 'unlocked' : 'locked';
        })->reverse();

        // get the placeable garden objects mapped by garden id
        $placeableUserObjects = $visibleGardens->mapWithKeys(function ($garden) {
            return [$garden->id => $garden->getUserGardenObjects(Auth::user(), 'placeable')];
        });

        return view('gardens.index', [
            'groups'               => $groups,
            'isGardenUnlocked'     => $isGardenUnlocked,
            'placeableUserObjects' => $placeableUserObjects,
        ]);
    }

    /**
     * Handles all garden actions (place, care, collect) in one endpoint for easier AJAX handling on the frontend.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function postAction(Request $request, GardenManager $service) {
        $action = $request->input('action');
        $data = $request->only([
            'plot_id', 'asset_id', 'asset_type', 'object_type', 'current_garden_id',
        ]);
        switch ($action) {
            case 'place':
                // place item / object / modifier in the plot
                if (!$service->place(Auth::user(), $data)) {
                    debug($service->errors()->getMessages()['error']);

                    return response()->json([
                        'success' => false,
                        'message' => 'Failed to place item.',
                        'errors'  => $service->errors()->getMessages()['error'],
                    ]);
                }
                break;
            case 'care':
                // interact with a plot, watering, feeding, etc.
                break;
            case 'collect':
                // collect the object from the plot
                break;
            default:
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid action.',
                ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Action performed successfully.',
        ]);
    }

    /**
     * Cares for all plots of a certain garden in one go.
     */
    public function postCareAll(Request $request, GardenManager $service) {
        // todo
        // this is going to be a regular request not ajax
    }

    /**
     * Collect all in one go of a certain garden in one go.
     */
    public function postCollectAll(Request $request, GardenManager $service) {
        // todo
        // this is going to be a regular request not ajax
        if ($count = $service->collectAll(Auth::user(), $type)) {
            flash('Collected '.$count.' successfully!')->success();
        } else {
            flash('Nothing to collect!')->warning();
        }

        return redirect()->back();
    }

    /**
     * Checks if any plants have died since the user has last logged on.
     */
    public function checkPlots() {
        // TODO
        // this is only for seeds (animals don't die (hopefully))
        // $check = 0;
        // $enabled_types = array_filter(config('lorekeeper.garden'), function ($type) {
        //     return $type['is_die_enabled'];
        // });
        // foreach (UserGardenPlot::where('user_id', Auth::user()->id)->whereIn('plot_id', GardenPlot::whereIn('plot_type', array_keys($enabled_types))->pluck('id'))->get() as $plot) {
        //     if (!config('lorekeeper.garden.'.$plot->plot->plot_type.'.is_die_enabled')) {
        //         return;
        //     } // for pussies /j
        //     if (!$plot->water_at || !$plot->item_id) {
        //         continue;
        //     }
        //     $water = Carbon::parse($plot->water_at)->addHours(config('lorekeeper.garden.seed.die_time'));
        //     if ($water < Carbon::now()) {
        //         $plot->is_dead = 1;
        //         $plot->save();

        //         $check = 1;
        //     }
        // }
        // if ($check) {
        //     flash('One or more of your plants has died due to lack of watering.')->error();
        // }
    }

    /**********************************************************************************************

        PRIVATE FUNCTIONS

    **********************************************************************************************/
}
