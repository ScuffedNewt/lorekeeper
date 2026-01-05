<?php

namespace App\Http\Controllers;

use App\Models\Runbook;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class RunbookController extends Controller {
    /*
    |--------------------------------------------------------------------------
    | Runbook Controller
    |--------------------------------------------------------------------------
    |
    | Displays site runbooks, editable from the admin panel.
    |
    */

    /**
     * Shows the runbook with the given title.
     *
     * @param string $title
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getRunbook($title) {
        // title is transformed using str_replace(' ', '-', strtolower($this->title))
        // so we need apply that transformation in the filtering and find the correct runbook
        $runbook = Runbook::visible(Auth::user() ?? null)->whereRaw('LOWER(REPLACE(title, " ", "-")) = ?', [strtolower($title)])->first();
        if (!$runbook || $runbook->type == 'Subsection') {
            abort(404);
        }

        return view('runbooks.runbook', ['runbook' => $runbook]);
    }
}
