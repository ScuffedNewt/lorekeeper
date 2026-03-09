<?php

namespace App\Http\Controllers\Admin\Stats;

use App\Http\Controllers\Controller;
use App\Models\Item\Item;
use App\Models\Level\Level;
use App\Services\Stat\LevelService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LevelController extends Controller {
    /**
     * Gets the levels page.
     *
     * @param mixed $type
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getLevels(Request $request, $type = 'Character') {
        $levels = Level::ordered($type);

        //$page = (int) request('page', 1);
        //$perPage = 20;

        return view('admin.levels.levels', [
            'type'   => $type,
            'levels' => $levels->paginate(20)->appends($request->query()),
        ]);
    }
    
    /**
     * Shows the create level page.
     *
     * @param mixed $type
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getCreateLevel($type = 'Character') {
        return view('admin.levels.create_edit_level', [
            'type'   => $type,
            'level'  => new Level,
            'levels' => Level::where('level_type', $type)->pluck('name', 'id'),
        ]);
    }

    /**
     * Shows the edit level page.
     *
     * @param mixed $id
     * @param mixed $type
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getEditLevel($type, $id) {
        $level = Level::find($id);
        if (!$level) {
            abort(404);
        }

        return view('admin.levels.create_edit_level', [
            'type'   => strtolower($level->level_type),
            'level'  => $level,
            'levels' => Level::where('level_type', $level->level_type)->where('id', '!=', $level->id)->pluck('name', 'id'),
        ]);
    }

    /**
     * Creates or edits an item.
     *
     * @param mixed|null $id
     * @param mixed      $type
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postCreateEditLevel(Request $request, LevelService $service, $type = 'Character', $id = null) {
        $id ? $request->validate(Level::$updateRules) : $request->validate(Level::$createRules);
        $data = $request->only([
            'exp_required', 'stat_points', 'rewardable_type', 'rewardable_id', 'quantity', 'description',
            'previous_level_id', 'name', 'image',
        ]);
        if ($id && $service->updateLevel(Level::find($id), $data)) {
            flash('Level updated successfully.')->success();
        } elseif (!$id && $level = $service->createLevel($data, $type, Auth::user())) {
            flash('Level created successfully.')->success();

            return redirect()->to('admin/levels/'.$type.'/edit/'.$level->id);
        } else {
            foreach ($service->errors()->getMessages()['error'] as $error) {
                flash($error)->error();
            }
        }

        return redirect()->back();
    }

    /**
     * Gets the level deletion modal.
     *
     * @param mixed $id
     * @param mixed $type
     */
    public function getDeleteLevel($type, $id) {
        $level = Level::find($id);

        return view('admin.levels._delete_level', [
            'level' => $level,
        ]);
    }

    /**
     * Creates or edits an level.
     *
     * @param mixed $id
     * @param mixed $type
     */
    public function postDeleteLevel(Request $request, LevelService $service, $type, $id) {
        $type = ucfirst($type);

        if ($id && $service->deleteLevel($type, Level::find($id))) {
            flash('Level deleted successfully.')->success();
        } else {
            foreach ($service->errors()->getMessages()['error'] as $error) {
                flash($error)->error();
            }
        }

        return redirect()->to('admin/levels/'.strtolower($type));
    }
}
