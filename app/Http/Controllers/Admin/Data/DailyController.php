<?php

namespace App\Http\Controllers\Admin\Data;

use App\Http\Controllers\Controller;
use App\Models\Currency\Currency;
use App\Models\Daily\Daily;
use App\Models\Item\Item;
use App\Models\Loot\LootTable;
use App\Models\Raffle\Raffle;
use App\Services\DailyService;
use Auth;
use Illuminate\Http\Request;

class DailyController extends Controller {
    /*
    |--------------------------------------------------------------------------
    | Admin / Daily Controller
    |--------------------------------------------------------------------------
    |
    | Handles creation/editing of dailies.
    |
    */

    /**
     * Shows the daily index.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getIndex() {
        return view('admin.dailies.dailies', [
            'dailies' => Daily::orderBy('sort', 'DESC')->get(),
        ]);
    }

    /**
     * Shows the create daily page.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getCreateDaily() {
        return view('admin.dailies.create_edit_daily', [
            'daily'      => new Daily,
            'items'      => Item::orderBy('name')->pluck('name', 'id'),
            'currencies' => Currency::where('is_user_owned', 1)->orderBy('name')->pluck('name', 'id'),
            'tables'     => LootTable::orderBy('name')->pluck('name', 'id'),
            'raffles'    => Raffle::where('rolled_at', null)->where('is_active', 1)->orderBy('name')->pluck('name', 'id'),
        ]);
    }

    /**
     * Shows the edit daily page.
     *
     * @param int $id
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getEditDaily($id) {
        $daily = Daily::find($id);
        if (!$daily) {
            abort(404);
        }

        return view('admin.dailies.create_edit_daily', [
            'daily'      => $daily,
            'items'      => Item::orderBy('name')->pluck('name', 'id'),
            'currencies' => Currency::where('is_user_owned', 1)->orderBy('name')->pluck('name', 'id'),
            'tables'     => LootTable::orderBy('name')->pluck('name', 'id'),
            'raffles'    => Raffle::where('rolled_at', null)->where('is_active', 1)->orderBy('name')->pluck('name', 'id'),
        ]);
    }

    /**
     * Creates or edits a daily.
     *
     * @param App\Services\DailyService $service
     * @param int|null                  $id
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postCreateEditDaily(Request $request, DailyService $service, $id = null) {
        $id ? $request->validate(Daily::$updateRules) : $request->validate(Daily::$createRules);
        $data = $request->only([
            'name', 'description', 'image', 'button_image', 'remove_image', 'remove_button_image', 'has_image', 'has_button_image', 'step', 'rewardable_type', 'rewardable_id', 'quantity',
            'is_active', 'is_progressable', 'is_timed_daily', 'start_at', 'end_at', 'daily_timeframe', 'progress_display', 'is_loop', 'is_streak', 'type',
            'wheel_image', 'background_image', 'stopper_image', 'remove_wheel', 'remove_background', 'remove_stopper', 'size', 'alignment', 'segment_number', 'segment_style', 'text_orientation', 'text_fontsize',
            'fee', 'currency_id',
        ]);
        if ($id && $service->updateDaily(Daily::find($id), $data, Auth::user())) {
            flash('The '.__('dailies.daily').' was updated successfully.')->success();
        } elseif (!$id && $daily = $service->createDaily($data, Auth::user())) {
            flash('The '.__('dailies.daily').' was created successfully.')->success();

            return redirect()->to('admin/data/dailies/edit/'.$daily->id);
        } else {
            foreach ($service->errors()->getMessages()['error'] as $error) {
                flash($error)->error();
            }
        }

        return redirect()->back();
    }

    /**
     * Gets the daily deletion modal.
     *
     * @param int $id
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getDeleteDaily($id) {
        $daily = Daily::find($id);

        return view('admin.dailies._delete_daily', [
            'daily' => $daily,
        ]);
    }

    /**
     * Deletes a daily.
     *
     * @param App\Services\DailyService $service
     * @param int                       $id
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postDeleteDaily(Request $request, DailyService $service, $id) {
        if ($id && $service->deleteDaily(Daily::find($id))) {
            flash('The '.__('dailies.daily').' was deleted successfully.')->success();
        } else {
            foreach ($service->errors()->getMessages()['error'] as $error) {
                flash($error)->error();
            }
        }

        return redirect()->to('admin/data/dailies');
    }

    /**
     * Sorts dailies.
     *
     * @param App\Services\DailyService $service
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postSortDaily(Request $request, DailyService $service) {
        if ($service->sortDaily($request->get('sort'))) {
            flash('The '.__('dailies.daily').' order was updated successfully.')->success();
        } else {
            foreach ($service->errors()->getMessages()['error'] as $error) {
                flash($error)->error();
            }
        }

        return redirect()->back();
    }
}
