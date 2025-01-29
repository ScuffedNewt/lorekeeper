<?php

namespace App\Http\Controllers\Admin\Stats;

use App\Http\Controllers\Controller;
use App\Models\Claymore\Gear;
use App\Models\Claymore\Weapon;
use App\Models\Currency\Currency;
use App\Models\Item\Item;
use App\Models\Item\ItemCategory;
use App\Models\Level\Level;
use App\Models\Loot\LootTable;
use App\Models\Pet\Pet;
use App\Models\Raffle\Raffle;
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
        $query = Level::query()->where('level_type', $type);
        $data = $request->only(['level']);
        if (isset($data['level'])) {
            $query->where('level', 'LIKE', '%'.$data['level'].'%');
        }

        return view('admin.levels.levels', [
            'type'   => $type,
            'levels' => $query->paginate(20)->appends($request->query()),
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
        if ($type == 'Character') {
            $categories = ItemCategory::where('is_character_owned', '1')->orderBy('sort', 'DESC')->get();
            $itemOptions = Item::whereIn('item_category_id', $categories->pluck('id'));
            $items = Item::whereIn('id', $itemOptions->pluck('id'))->pluck('name', 'id');
        } else {
            $items = Item::orderBy('name')->pluck('name', 'id');
        }

        return view('admin.levels.create_edit_level', [
            'type'       => $type,
            'level'      => new Level,
            'items'      => $items,
            'currencies' => Currency::where('is_user_owned', 1)->orderBy('name')->pluck('name', 'id'),
            'tables'     => LootTable::orderBy('name')->pluck('name', 'id'),
            'raffles'    => Raffle::where('rolled_at', null)->where('is_active', 1)->orderBy('name')->pluck('name', 'id'),
            'pets'       => Pet::orderBy('name')->pluck('name', 'id'),
            'gears'      => Gear::orderBy('name')->pluck('name', 'id'),
            'weapons'    => Weapon::orderBy('name')->pluck('name', 'id'),
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

        if (strtolower($level->level_type) == 'character') {
            $categories = ItemCategory::where('is_character_owned', '1')->orderBy('sort', 'DESC')->get();
            $itemOptions = Item::whereIn('item_category_id', $categories->pluck('id'));
            $items = Item::whereIn('id', $itemOptions->pluck('id'))->pluck('name', 'id');
        } else {
            $items = Item::orderBy('name')->pluck('name', 'id');
        }

        return view('admin.levels.create_edit_level', [
            'type'       => strtolower($level->level_type),
            'level'      => $level,
            'items'      => $items,
            'currencies' => Currency::where('is_user_owned', 1)->orderBy('name')->pluck('name', 'id'),
            'tables'     => LootTable::orderBy('name')->pluck('name', 'id'),
            'raffles'    => Raffle::where('rolled_at', null)->where('is_active', 1)->orderBy('name')->pluck('name', 'id'),
            'pets'       => Pet::orderBy('name')->pluck('name', 'id'),
            'gears'      => Gear::orderBy('name')->pluck('name', 'id'),
            'weapons'    => Weapon::orderBy('name')->pluck('name', 'id'),
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
            'level', 'exp_required', 'stat_points', 'rewardable_type', 'rewardable_id', 'quantity', 'description',
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
