<?php

namespace App\Http\Controllers\Users;

use Illuminate\Http\Request;

use DB;
use Auth;
use Route;
use Settings;
use App\Models\User\User;
use App\Models\Character\Character;
use App\Models\Item\Item;
use App\Models\Character\Sublist;
use App\Http\Controllers\Controller;

use App\Models\Pairing\Pairing;
use App\Services\PairingManager;

class PairingController extends Controller
{
   /**
     * Shows the user's Pairings.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getPairings()
    {
        $user = Auth::user();
        $pairings = Pairing::where('user_id', $user->id)->whereNotIn('status', ['REJECTED', 'USED'])->orderBy('id', 'DESC')->get();
        $approvals = Pairing::where(function ($query) {
            $user = Auth::user();
            $character_ids = $user->characters()->pluck('id')->toArray();
            $query->whereIn('character_1_id', $character_ids)->orWhereIn('character_2_id', $character_ids);
        })->where('user_id','!=',$user->id)->whereIn('status', ['OPEN'])->get();

        $closed = Pairing::where('user_id', $user->id)->whereIn('status', ['REJECTED', 'USED'])->get();

        $all_items = $user->items()->where('count', ">", 0)->get();
        $pairing_item_ids = [];
        foreach($all_items as $item){
            if($item->tags()->where('tag', 'pairing')->exists()) $pairing_item_ids[] = $item->id;
        }

        $items = Item::whereIn('id', $pairing_item_ids)->orderBy('name')->pluck('name', 'id');

        return view('home.pairings', [
            'pairings' => $pairings,
            'approvals' => $approvals,
            'closed' => $closed,
            'items' => $items,
            'sublists' => Sublist::orderBy('sort', 'DESC')->get(),

        ]);
    }

    /**
     * Create a new pairing.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function createPairings(Request $request, PairingManager $service)
    {
        $pairings = Pairing::where('user_id', Auth::user()->id)->get();

        if ($service->createPairing($request->character_1_code, $request->character_2_code,$request->item_id, Auth::user())) {
            flash('Pairing created!')->success();
            return redirect()->back();
        }
        else {
            foreach($service->errors()->getMessages()['error'] as $error) flash($error)->error();
        }
        return redirect()->back();
    }

    /**
     * Approves a pairing.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function approvePairing(Request $request, PairingManager $service)
    {
        if ($service->approvePairing($request->pairing_id, Auth::user())) {
            flash('Pairing approved!')->success();
            return redirect()->back();
        }
        else {
            foreach($service->errors()->getMessages()['error'] as $error) flash($error)->error();
        }
        return redirect()->back();
    }

    /**
     * Rejects a pairing.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function rejectPairing(Request $request, PairingManager $service)
    {
        if ($service->rejectPairing($request->pairing_id, Auth::user())) {
            flash('Pairing Rejected!')->success();
            return redirect()->back();
        }
        else {
            foreach($service->errors()->getMessages()['error'] as $error) flash($error)->error();
        }
        return redirect()->back();
    }

    /**
     * Creates a MYO from the pairing.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function createMyo(Request $request, PairingManager $service)
    {
        $myosCreated = $service->createMyos($request->pairing_id, Auth::user());
        if (is_numeric($myosCreated)) {
            flash('Congrats!! '.$myosCreated.' Pairing MYO Slots have been created!')->success();
            return redirect()->back();
        }
        else {
            foreach($service->errors()->getMessages()['error'] as $error) flash($error)->error();
        }
        return redirect()->back();
    }

}
