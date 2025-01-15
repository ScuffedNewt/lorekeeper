<?php

namespace App\Http\Controllers\Admin\Stats;

use App\Http\Controllers\Controller;
use App\Models\Species\Species;
use App\Models\Species\Subtype;
use App\Models\Stat\Stat;
use App\Services\Stat\StatService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class StatController extends Controller {
    /**
     * Gets the stats index page.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getIndex(Request $request) {
        $query = Stat::query();
        $data = $request->only(['name']);
        if (isset($data['name'])) {
            $query->where('name', 'LIKE', '%'.$data['name'].'%');
        }

        return view('admin.stats.stats', [
            'stats' => $query->paginate(20)->appends($request->query()),
        ]);
    }

    /**
     * Shows the create stat page.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getCreateStat() {
        return view('admin.stats.create_edit_stat', [
            'stat' => new Stat,
        ]);
    }

    /**
     * Shows the edit stat page.
     *
     * @param mixed $id
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getEditStat($id) {
        $stat = Stat::find($id);
        if (!$stat) {
            abort(404);
        }

        $subtypes = Subtype::orderBy('sort', 'DESC')->get()->keyBy(function ($subtype) {
            return $subtype->id;
        })->map(function ($subtype) {
            return $subtype->name.' ('.$subtype->species->name.')';
        })->toArray();

        return view('admin.stats.create_edit_stat', [
            'stat'      => $stat,
            'specieses' => Species::orderBy('specieses.sort', 'DESC')->pluck('name', 'id')->toArray(),
            'subtypes'  => $subtypes,
        ]);
    }

    /**
     * Creates or edits an stat.
     *
     * @param mixed|null $id
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postCreateEditStat(Request $request, StatService $service, $id = null) {
        $id ? $request->validate(Stat::$updateRules) : $request->validate(Stat::$createRules);
        $data = $request->only([
            'name', 'abbreviation', 'base', 'increment', 'multiplier', 'max_level', 'types', 'type_ids', 'colour', 'base_types', 'base_type_ids', 'base_values', 'icon',
        ]);
        if ($id && $service->updateStat(Stat::find($id), $data)) {
            flash('Stat updated successfully.')->success();
        } elseif (!$id && $stat = $service->createStat($data, Auth::user())) {
            flash('Stat created successfully.')->success();

            return redirect()->to('admin/stats/edit/'.$stat->id);
        } else {
            foreach ($service->errors()->getMessages()['error'] as $error) {
                flash($error)->error();
            }
        }

        return redirect()->back();
    }

    /**
     * Gets the stat deletion modal.
     *
     * @param mixed $id
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getDeleteStat($id) {
        $stat = Stat::find($id);

        return view('admin.stats._delete_stat', [
            'stat' => $stat,
        ]);
    }

    /**
     * Creates or edits an stat.
     *
     * @param mixed $id
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postDeleteStat(Request $request, StatService $service, $id) {
        if ($id && $service->deleteStat(Stat::find($id))) {
            flash('Stat deleted successfully.')->success();
        } else {
            foreach ($service->errors()->getMessages()['error'] as $error) {
                flash($error)->error();
            }
        }

        return redirect()->to('admin/stats');
    }
}
