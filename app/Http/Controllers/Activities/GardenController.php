<?php

namespace App\Http\Controllers\Activities;

use App\Models\Garden\Garden;
use App\Models\Garden\UserGardenPlot;
use App\Models\User\User;
use App\Services\Garden\GardenManager;
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
        $modifierUserObjects = $visibleGardens->mapWithKeys(function ($garden) {
            return [$garden->id => $garden->getUserGardenObjects(Auth::user(), 'modifier')];
        });

        return view('gardens.index', [
            'groups'               => $groups,
            'isGardenUnlocked'     => $isGardenUnlocked,
            'modifierUserObjects'  => $modifierUserObjects,
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
            'plot_id', 'asset_id', 'asset_type', 'object_type', 'current_garden_id', 'collection_outcome',
        ]);

        $message = null;
        switch ($action) {
            case 'place':
                // place item / object / modifier in the plot
                if (!$message = $service->place(Auth::user(), $data)) {
                    debug($service->errors()->getMessages()['error']);

                    return response()->json([
                        'success' => false,
                        'message' => 'Failed to place item.',
                        'errors'  => $service->errors()->getMessages()['error'],
                    ]);
                }
                break;
            case 'tend':
                // interact with a plot, watering, feeding, etc.
                if (!$message = $service->tend(Auth::user(), $data)) {
                    debug($service->errors()->getMessages()['error']);

                    return response()->json([
                        'success' => false,
                        'message' => 'Failed to tend to plot.',
                        'errors'  => $service->errors()->getMessages()['error'],
                    ]);
                }
                break;
            case 'collect':
                // collect the object from the plot
                if (!$message = $service->collect(Auth::user(), $data)) {
                    debug($service->errors()->getMessages()['error']);

                    return response()->json([
                        'success' => false,
                        'message' => 'Failed to collect from plot.',
                        'errors'  => $service->errors()->getMessages()['error'],
                    ]);
                }
                break;
            case 'remove_animal':
                if (!$service->removeAnimal(Auth::user(), $data['plot_id'])) {
                    return response()->json(['success' => false, 'message' => 'Failed to remove animal.', 'errors' => $service->errors()->getMessages()['error']]);
                }
                $message = 'Animal returned to your inventory.';
                break;
            default:
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid action.',
                ]);
        }

        $userPlot = $service->getUserPlot(Auth::user(), $data);

        return response()->json([
            'success' => true,
            'message' => $message,
            'view'    => $userPlot?->object ? view('gardens._object', [
                'plot'       => $userPlot?->plot,
                'userPlot'   => $userPlot,
                'object'     => $userPlot?->object,
            ])->render() : '',
            'state'       => $userPlot?->started_at ? 'filled' : 'empty',
            'actionReady' => $userPlot?->actionReady ?? false,
            'canCollect'  => $userPlot?->canCollect ?? false,
            'isPerished'  => $userPlot?->isPerished ?? false,
        ]);
    }

    /**
     * Returns fresh server-rendered state for a plot.
     *
     * @param int $plotId
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getPlotState($plotId) {
        $userPlot = UserGardenPlot::findOrFail($plotId);
        if ($userPlot->user_id != Auth::id() && !config('lorekeeper.garden.allow_users_to_visit_gardens')) {
            abort(403);
        }

        return response()->json([
            'view' => $userPlot->isPerished
                ? view('gardens._perished', ['userPlot' => $userPlot])->render()
                : ($userPlot->object ? view('gardens._object', [
                    'plot'     => $userPlot->plot,
                    'userPlot' => $userPlot,
                    'object'   => $userPlot->object,
                ])->render() : '<span class="icon">➕</span>Empty plot'),
            'state'       => $userPlot->started_at ? 'filled' : 'empty',
            'actionReady' => $userPlot->actionReady,
            'canCollect'  => $userPlot->canCollect,
            'isPerished'  => $userPlot->isPerished,
        ]);
    }

    /**
     * Clears a perished seed from one of the authenticated user's plots.
     *
     * @param App\Services\Garden\GardenManager $service
     * @param int                               $plotId
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postClearPerished(GardenManager $service, $plotId) {
        if ($service->clearPerished(Auth::user(), $plotId)) {
            flash('Perished seed cleared.')->success();
        } else {
            foreach ($service->errors()->getMessages()['error'] as $error) {
                flash($error)->error();
            }
        }

        return redirect()->back();
    }

    /**
     * Cares for all plots of a certain garden in one go.
     *
     * @param App\Services\Garden\GardenManager $service
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postCareAll(Request $request, GardenManager $service) {
        $count = $service->careAll(Auth::user(), $request->input('garden_id'));
        flash($count ? 'Cared for '.$count.' plot'.($count == 1 ? '' : 's').'.' : 'No plots were ready for care.')->success();

        return redirect()->back();
    }

    /**
     * Collects all ready plots in a garden in one go.
     *
     * @param App\Services\Garden\GardenManager $service
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postCollectAll(Request $request, GardenManager $service) {
        if ($count = $service->collectAll(Auth::user(), $request->input('garden_id'))) {
            flash('Collected '.$count.' successfully!')->success();
        } else {
            flash('Nothing to collect!')->warning();
        }

        return redirect()->back();
    }

    /**
     * Waters another user's ready seed.
     *
     * @param App\Services\Garden\GardenManager $service
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function postGuestTend(Request $request, GardenManager $service) {
        if ($message = $service->guestTend(Auth::user(), $request->input('plot_id'))) {
            $limit = max(0, (int) config('lorekeeper.garden.user_guest_water_limit'));
            Auth::user()->load('activity');

            return response()->json([
                'success'   => true,
                'message'   => $message,
                'remaining' => $limit ? max(0, $limit - (Auth::user()->activity?->garden_guest_waters_today ?? 0)) : null,
            ]);
        }

        return response()->json(['success' => false, 'message' => $service->errors()->first('error')], 422);
    }

    /**********************************************************************************************

        PRIVATE FUNCTIONS

    **********************************************************************************************/
}
