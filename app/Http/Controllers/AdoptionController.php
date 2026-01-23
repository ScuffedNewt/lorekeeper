<?php

namespace App\Http\Controllers;

use App\Models\Adoption\Adoption;
use App\Models\Adoption\AdoptionCurrency;
use App\Models\Adoption\AdoptionLog;
use App\Models\Adoption\AdoptionStock;
use App\Models\Character\Character;
use App\Models\Currency\Currency;
use App\Services\AdoptionManager;
use Auth;
use Illuminate\Http\Request;

class AdoptionController extends Controller {
    /*
    |--------------------------------------------------------------------------
    | Adoption Controller
    |--------------------------------------------------------------------------
    |
    | Handles viewing the adoption index, adoptions and purchasing from adoptions.
    |
    */

    /**
     * Shows a adoption.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getAdoption() {
        $adoption = Adoption::orderBy('id', 'ASC')->first();
        if (!$adoption) {
            abort(404);
        }
        //check if the adoption center is active
        if (!$adoption->is_active) {
            abort(404);
        }

        return view('adoptions.adoption', [
            'adoption'   => $adoption,
            'adoptions'  => Adoption::where('is_active', 1)->get(),
            'currencies' => Currency::whereIn('id', AdoptionCurrency::pluck('currency_id')->toArray())->get()->keyBy('id'),
            'stocks'     => AdoptionStock::visible()->get(),
        ]);
    }

    /**
     * Gets the adoption stock modal.
     *
     * @param App\Services\AdoptionManager $service
     * @param int                          $id
     * @param int                          $stockId
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getAdoptionStock(AdoptionManager $service, $id, $stockId) {
        $adoption = Adoption::orderBy('id', 'ASC')->first();
        //check if the adoption center is active
        if (!$adoption->is_active) {
            abort(404);
        }
        $stock = AdoptionStock::with('character')->where('character_id', $stockId)->where('adoption_id', $id)->first();
        if (!$adoption) {
            abort(404);
        }

        return view('adoptions._stock_modal', [
            'adoption' => $adoption,
            'stock'    => $stock,
        ]);
    }

    /**
     * Buys an character from a adoption.
     *
     * @param App\Services\AdoptionManager $service
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postBuy(Request $request, AdoptionManager $service) {
        $request->validate(AdoptionLog::$createRules);
        if ($service->buyStock($request->only(['stock_id', 'adoption_id', 'slug', 'bank', 'currency_id']), Auth::user())) {
            flash('Successfully purchased character.')->success();
        } else {
            foreach ($service->errors()->getMessages()['error'] as $error) {
                flash($error)->error();
            }
        }

        return redirect()->back();
    }

    /**
     * Shows the user's purchase history.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getPurchaseHistory() {
        return view('adoptions.purchase_history', [
            'logs'     => Auth::user()->getAdoptionLogs(0),
            'adoption' => Adoption::orderBy('id', 'ASC')->first(),
        ]);
    }
}
