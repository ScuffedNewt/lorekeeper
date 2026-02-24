<?php

namespace App\Http\Controllers\Users;

use App\Http\Controllers\Controller;
use App\Models\Level\Level;
use App\Models\User\User;
use App\Services\Stat\LevelManager;
use Illuminate\Support\Facades\Auth;

class UserStatController extends Controller {
    /**
     * Shows the user's level page.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getIndex() {
        if (!config('lorekeeper.claymores_and_companions.visibility_settings.user_levels') && !config('lorekeeper.claymores_and_companions.visibility_settings.character_stats')) {
            abort(404);
        }

        $user = Auth::user();
        // create a user level if one doesn't exist
        if (!$user->level) {
            $user->level()->create([
                'user_id' => $user->id,
            ]);
        }

        return view('home.stats', [
            'user'       => $user,
            'characters' => $user->characters()->pluck('slug', 'id'),
        ]);
    }

    /**
     * Level up the user.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postLevelUp(LevelManager $service) {
        if (!config('lorekeeper.claymores_and_companions.visibility_settings.user_levels')) {
            abort(404);
        }

        if ($service->levelUp(Auth::user())) {
            flash('Successfully leveled up!')->success();
        } else {
            foreach ($service->errors()->getMessages()['error'] as $error) {
                flash($error)->error();
            }
        }

        return redirect()->back();
    }
}
