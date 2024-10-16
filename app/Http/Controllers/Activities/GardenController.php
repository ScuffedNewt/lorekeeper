<?php

namespace App\Http\Controllers\Activities;

use App\Models\Garden\Garden;
use App\Models\Garden\GardenObject;
use App\Models\Garden\GardenPlot;
use App\Models\User\User;
use App\Models\User\UserGardenPlot;
use App\Models\User\UserItem;
use App\Services\Garden\PlotManager;
use App\Services\LimitManager;
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
        $is_garden_unlocked = Garden::visible(Auth::user() ?? null)->get()->mapWithKeys(function ($garden) {
            return [$garden->id => (new LimitManager)->checkLimits($garden)];
        });
        $groups = Garden::visible(Auth::user() ?? null)->get()->groupBy(function ($garden) use ($is_garden_unlocked) {
            return $is_garden_unlocked[$garden->id] ? 'unlocked' : 'locked';
        })->reverse();

        $user_garden_objects = [];
        foreach (GardenObject::all() as $gardenObject) {
            switch ($gardenObject->object_model) {
                case 'App\Models\Item\Item':
                    if (!isset($user_garden_objects['items'])) {
                        $user_garden_objects['items'] = collect();
                    }
                    $user_garden_objects['items']->push(
                        UserItem::where('user_id', Auth::user()->id)->where('item_id', $gardenObject->object_id)->where('count', '>', 0)->get()
                    );
                    break;

                default:
                    flash('Unknown garden object type: ' . $gardenObject->object_model)->error();
                    return redirect()->back();
                    break;
            }
        }
        foreach ($user_garden_objects as $key => $user_garden_object) {
            $user_garden_objects[$key] = $user_garden_object->flatten();
        }

        return view('gardens.index', [
            'groups'              => $groups,
            'is_garden_unlocked'  => $is_garden_unlocked,
            'user_garden_objects' => $user_garden_objects,
        ]);
    }

    /**
     * Shows the garden page.
     *
     * @param mixed $name
     */
    public function getGarden($name) {
        $garden = Garden::visible()->where('name', str_replace('-', ' ', $name))->first();
        if (!$garden) {
            abort(404);
        }

        return view('gardens.garden', [
            'garden' => $garden,
        ]);
    }

    /**
     * Plant seed | Place coop / barn animal.
     */
    public function postPlantSeed(Request $request) {
        $data = $request->only(['item_id', 'plot_id', 'type']);
        $service = new PlotManager;
        if ($service->place(Auth::user(), $data)) {
            flash($data['type'] == 'seed' ? 'Planted successfully!' : 'Placed successfully!')->success();
            $response = [
                'status' => 'success',
                'msg'    => $request->message,
            ];

            return response()->json($response);
        } else {
            foreach ($service->errors()->getMessages()['error'] as $error) {
                flash($error)->error();
            }
            $response = [
                'status' => 'error',
                'error'  => $service->errors()->getMessages()['error'],
                'msg'    => $request->message,
            ];

            return response()->json($response);
        }
    }

    /**
     * Add Modifier.
     */
    public function postAddModifier(Request $request) {
        $data = $request->only(['mod_id', 'plot_id']);
        $service = new PlotManager;
        if ($service->modify(Auth::user(), $data)) {
            flash('Added modifier successfully!')->success();
            $response = [
                'status' => 'success',
                'msg'    => $request->message,
            ];

            return response()->json($response);
        } else {
            foreach ($service->errors()->getMessages()['error'] as $error) {
                flash($error)->error();
            }
            $response = [
                'status' => 'error',
                'msg'    => $request->message,
            ];

            return response()->json($response);
        }
    }

    /**
     * Water plot | feed animal.
     *
     * @param mixed $id
     */
    public function postAction($id) {
        $plot = UserGardenPlot::findOrFail($id);
        $service = new PlotManager;
        if ($plot->actionReady) {
            if ($service->action(Auth::user(), $plot, false)) {
                flash(($plot->plot_type == 'seed' ? $plot->actionWord : 'f').'ed successfully!')->success();
            } else {
                foreach ($service->errors()->getMessages()['error'] as $error) {
                    flash($error)->error();
                }
            }
        } else {
            flash('Not ready to water!')->error();
        }

        return redirect()->to(($plot->plot->plot_type == 'seed' ? 'garden#farm' : 'garden#'.($plot->plot->plot_type)));
    }

    /**
     * waters / feeds all plots that are ready.
     *
     * @param mixed $type
     */
    public function postActionAll(PlotManager $service, $type) {
        if ($count = $service->action(Auth::user(), $type, true)) {
            flash(($type == 'seed' ? 'Watered' : 'Fed').' '.$count.' plots successfully!')->success();
        } else {
            flash('Nothing to water!')->warning();
        }

        return redirect()->back();
    }

    /**
     * when the feeding | waterings have met the requirements and the user gets their reward.
     *
     * @param mixed $id
     */
    public function postClaim(PlotManager $service, $id) {
        $plot = UserGardenPlot::findOrFail($id);
        if ($plot->finishReady) {
            if ($service->claim(Auth::user(), $plot)) {
                flash('Claimed successfully!')->success();
            } else {
                foreach ($service->errors()->getMessages()['error'] as $error) {
                    flash($error)->error();
                }
            }
        } else {
            flash('Not ready to claim!')->error();
        }

        return redirect()->to(($plot->plot->plot_type == 'seed' ? 'garden#farm' : 'garden#'.$plot->plot->plot_type));
    }

    /**
     * Collect all in one go of a certain type of plot.
     *
     * @param mixed $type
     */
    public function postCollectAll(PlotManager $service, $type) {
        if ($count = $service->collectAll(Auth::user(), $type)) {
            flash('Collected '.$count.' successfully!')->success();
        } else {
            flash('Nothing to collect!')->warning();
        }

        return redirect()->back();
    }

    /******************************************************************************************************
     *  PLOTS
     ******************************************************************************************************/

    /**
     * Buys the plots.
     *
     * @param mixed $id
     */
    public function postPurchasePlot($id, PlotManager $service) {
        $plot = GardenPlot::find($id);
        if (!$plot) {
            abort(404);
        }
        if ($service->purchasePlot($plot, Auth::user())) {
            flash('Plot purchased successfully.')->success();
        } else {
            foreach ($service->errors()->getMessages()['error'] as $error) {
                flash($error)->error();
            }
        }

        return redirect()->to('garden/#'.strtolower($plot->plot_type));
    }

    /**
     * Checks if any plants have died since the user has last logged on.
     */
    public function checkPlots() {
        // this is only for seeds (animals don't die (hopefully))
        $check = 0;
        $enabled_types = array_filter(config('lorekeeper.garden'), function ($type) {
            return $type['is_die_enabled'];
        });
        foreach (UserGardenPlot::where('user_id', Auth::user()->id)->whereIn('plot_id', GardenPlot::whereIn('plot_type', array_keys($enabled_types))->pluck('id'))->get() as $plot) {
            if (!config('lorekeeper.garden.'.$plot->plot->plot_type.'.is_die_enabled')) {
                return;
            } // for pussies /j
            if (!$plot->water_at || !$plot->item_id) {
                continue;
            }
            $water = Carbon::parse($plot->water_at)->addHours(config('lorekeeper.garden.seed.die_time'));
            if ($water < Carbon::now()) {
                $plot->is_dead = 1;
                $plot->save();

                $check = 1;
            }
        }
        if ($check) {
            flash('One or more of your plants has died due to lack of watering.')->error();
        }
    }

    /**
     * Get skull modal.
     *
     * @param mixed $id
     */
    public function getSkull($id) {
        $plot = UserGardenPlot::find($id);
        if (!$plot) {
            abort(404);
        }
        if ($plot->plot->plot_type == 'seed') {
            return view('garden._skull_modal', [
                'plot' => $plot,
            ]);
        } else {
            return view('garden._clear_barn_coop', [
                'plot' => $plot,
            ]);
        }
    }

    /**
     * @param mixed $id
     */
    public function destroyCrop($id, PlotManager $service) {
        $plot = UserGardenPlot::find($id);
        if (!$plot) {
            abort(404);
        }
        if ($service->clear(Auth::user(), $plot)) {
            if ($plot->plot->plot_type == 'seed') {
                flash('Crop destroyed successfully!')->success();
            } else {
                flash('Removed successfully!')->success();
            }
        } else {
            foreach ($service->errors()->getMessages()['error'] as $error) {
                flash($error)->error();
            }
        }

        return redirect()->to('garden/#'.$plot->plot->plot_type);
    }

    /**
     * Post clear plot.
     *
     * @param mixed $id
     */
    public function postClearPlot($id) {
        $plot = UserGardenPlot::findOrFail($id);
        $service = new PlotManager;
        if ($service->clear(Auth::user(), $plot)) {
            flash('Cleared successfully!')->success();

            return redirect()->to(($plot->plot->plot_type == 'seed' ? 'garden#farm' : 'garden#'.$plot->plot->plot_type));
        } else {
            foreach ($service->errors()->getMessages()['error'] as $error) {
                flash($error)->error();
            }
        }
    }
}
