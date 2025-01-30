<?php

namespace App\Http\Controllers\Characters;

use App\Http\Controllers\Controller;
use App\Models\Character\Character;
use App\Models\Character\CharacterStat;
use App\Models\Level\Level;
use App\Models\Stat\Stat;
use App\Services\Stat\LevelManager;
use App\Services\Stat\StatManager;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

class CharacterStatController extends Controller {
    /**
     * Create a new controller instance.
     */
    public function __construct() {
        $this->middleware(function ($request, $next) {
            $slug = Route::current()->parameter('slug');
            $query = Character::myo(0)->where('slug', $slug);
            if (!(Auth::check() && Auth::user()->hasPower('manage_characters'))) {
                $query->where('is_visible', 1);
            }
            $this->character = $query->first();
            if (!$this->character) {
                abort(404);
            }

            if (!$this->character->level) {
                $this->character->level()->create([
                    'character_id' => $this->character->id,
                ]);
            }

            $this->character->updateOwner();

            return $next($request);
        });
    }

    /**
     * Shows the character's stats page, which shows its level and stat information.
     *
     * @param mixed $slug
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getStats($slug) {
        $character = $this->character;

        $character->propagateStats();

        return view('character.stats.character_stats', [
            'character'  => $character,
        ]);
    }

    /**
     * Shows a character's specific stat information.
     *
     * @param mixed $slug
     * @param mixed $id
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getStat($slug, $id) {
        $character = $this->character;

        $stat = CharacterStat::find($id);

        return view('character.stats._stat', [
            'stat'       => $stat,
            'character'  => $character,
        ]);
    }

    /**
     * Shows the character's stats page, which shows its level, exp, stat and count logs.
     *
     * @param mixed $slug
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getStatLogs($slug) {
        return view('character.stats.character_stat_logs', [
            'character'       => $this->character,
            'exps'            => $this->character->getExpLogs(),
            'levels'          => $this->character->getLevelLogs(),
            'stat_transfers'  => $this->character->getStatTransferLogs(),
            'stat_levels'     => $this->character->getStatLevelLogs(),
            'counts'          => $this->character->getCountLogs(),
        ]);
    }

    /**
     * Character level up.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function postLevel(LevelManager $service) {
        $character = $this->character;
        if (!Auth::check() || (Auth::user()->id != $character->user_id && !Auth::user()->hasPower('manage_characters'))) {
            abort(404);
        }
        if (!$character) {
            abort(404);
        }

        if ($service->level($character)) {
            flash('Successfully leveled up!')->success();
        } else {
            foreach ($service->errors()->getMessages()['error'] as $error) {
                flash($error)->error();
            }
        }

        return redirect()->back();
    }

    /**
     * Level up a character's stat.
     *
     * @param mixed $slug
     * @param mixed $stat_id
     */
    public function postLevelStat(StatManager $service, $slug, $stat_id) {
        $character = $this->character;
        if (!Auth::check() || (Auth::user()->id != $character->user_id && !Auth::user()->hasPower('manage_characters'))) {
            abort(404);
        }
        $isStaff = Auth::user()->id != $character->user_id && Auth::user()->hasPower('manage_characters');
        $stat = Stat::find($stat_id);
        if ($service->levelCharacterStat($character, $stat, $isStaff)) {
            flash('Characters stat leveled successfully!')->success();
        } else {
            foreach ($service->errors()->getMessages()['error'] as $error) {
                flash($error)->error();
            }
        }

        return redirect()->back();
    }

    /**
     * edits the stat count (not the base stat).
     *
     * Admin only function.
     *
     * @param mixed $slug
     * @param mixed $id
     */
    public function postEditStatCurrentCount(Request $request, StatManager $service, $slug, $id) {
        $character = $this->character;
        if (!Auth::check() || (!Auth::user()->hasPower('manage_characters'))) {
            abort(404);
        }

        if ($service->editCharacterStatCurrentCount(Stat::find($id), $character, $request->get('current_count'))) {
            flash('Characters stat edited successfully!')->success();
        } else {
            foreach ($service->errors()->getMessages()['error'] as $error) {
                flash($error)->error();
            }
        }

        return redirect()->back();
    }

    /**
     * edits the base stat value.
     *
     * Admin only function.
     *
     * @param mixed $slug
     * @param mixed $id
     */
    public function postEditBaseStat(Request $request, StatManager $service, $slug, $id) {
        $character = $this->character;
        if (!Auth::check() || (!Auth::user()->hasPower('manage_characters'))) {
            abort(404);
        }

        if ($service->editCharacterStatBaseCount(Stat::find($id), $character, $request->get('count'))) {
            flash('Characters stat edited successfully!')->success();
        } else {
            foreach ($service->errors()->getMessages()['error'] as $error) {
                flash($error)->error();
            }
        }

        return redirect()->back();
    }
}
