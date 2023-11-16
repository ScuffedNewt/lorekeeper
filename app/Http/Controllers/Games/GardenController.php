<?php

namespace App\Http\Controllers\Games;

use App\Models\GardenPlot;
use App\Models\Item\ItemTag;
use App\Models\User\UserGardenPlot;
use App\Services\PlotManager;
use Auth;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class GardenController extends Controller {
    /**
     * return index.
     */
    public function getIndex() {
        //getting users seeds items
        $seedId = ItemTag::where('tag', 'seed')->where('is_active', 1); // Removed get()
        foreach ($seedId->get() as $seed) {
            if (isset($seed->data['start_month']) && isset($seed->data['end_month']) &&
            !Carbon::now()->between(
                Carbon::create()->day(1)->month($seed->data['start_month']),
                Carbon::create()->month($seed->data['end_month'])->endOfMonth()
            )) {
                // remove seed from array
                $seedId->where('id', '!=', $seed->id);
            }
        }
        $checkSeeds = Auth::user()->items()->whereIn('item_id', $seedId->pluck('item_id'))->where('count', '>', 0)->get()->groupBy(['item_id', 'id']);

        // coops & barns
        $coopId = ItemTag::where('tag', 'coop')->where('is_active', 1);
        $barnId = ItemTag::where('tag', 'barn')->where('is_active', 1);

        $checkCoops = Auth::user()->items()->whereIn('item_id', $coopId->pluck('item_id'))->where('count', '>', 0)->get()->groupBy(['item_id', 'id']);
        $checkBarns = Auth::user()->items()->whereIn('item_id', $barnId->pluck('item_id'))->where('count', '>', 0)->get()->groupBy(['item_id', 'id']);
        //

        //getting users modifer items
        $ModifiersId = ItemTag::where('tag', 'fertiliser')->where('is_active', 1); // Removed get()
        $checkModifiers = Auth::user()->items()->whereIn('item_id', $ModifiersId->pluck('item_id'))->where('count', '>', 0)->get()->groupBy(['item_id', 'id']);

        $this->checkPlots();

        return view('garden.index', [
            'userSeeds'     => $checkSeeds,
            'userCoops'     => $checkCoops,
            'userBarns'     => $checkBarns,
            'userModifiers' => $checkModifiers,
            'plots'         => GardenPlot::all(),
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
