<?php

namespace App\Http\Controllers\Users;

use App\Http\Controllers\Controller;
use App\Models\Character\Character;
use App\Models\Item\Item;
use App\Models\Item\ItemCategory;
use App\Models\Pairing\Pairing;
use App\Models\User\User;
use App\Models\User\UserItem;
use App\Services\PairingManager;
use Auth;
use Illuminate\Http\Request;

class PairingController extends Controller {
    /**
     * Shows the user's Pairings.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getPairings(Request $request) {
        $user = Auth::user();

        $type = $request->get('type');
        if (!$type) {
            $type = 'new';
        }

        $pairings = null;

        if ($type == 'approval') {
            $pairings = Pairing::where(function ($query) use ($user) {
                $character_ids = $user->characters()->pluck('id')->toArray();
                $query->whereIn('character_1_id', $character_ids)->orWhereIn('character_2_id', $character_ids);
            })->whereIn('status', ['PENDING'])->orderBy('id', 'DESC')->get()->paginate(10)->appends($request->query());
        }

        if ($type == 'pending') {
            $pairings = Pairing::where('user_id', $user->id)->whereIn('status', ['APPROVED'])->orderBy('id', 'DESC')->get()->paginate(10)->appends($request->query());
        }

        if ($type == 'closed') {
            $pairings = Pairing::where('user_id', $user->id)->whereIn('status', ['REJECTED', 'COMPLETE', 'CANCELLED'])->orderBy('id', 'DESC')->get()->paginate(10)->appends($request->query());
        }

        $user_items = $user->items()->where('count', '>', 0)->get()->pluck('id')->toArray();
        $user_pairing_items = UserItem::whereIn('item_id', $user_items)->whereIn('item_id', Item::whereRelation('tags', 'tag', 'pairing')->pluck('id')->toArray())->get();
        $user_boost_items = UserItem::whereIn('item_id', $user_items)->whereIn('item_id', Item::whereRelation('tags', 'tag', 'boost')->pluck('id')->toArray())->get();

        return view('home.pairings', [
            'characters'            => Character::visible()->myo(0)->orderBy('number', 'DESC')->get()->pluck('fullName', 'slug')->toArray(),
            'pairings'              => $pairings,
            'user_pairing_items'    => $user_pairing_items,
            'user_boost_items'      => $user_boost_items,
            'categories'            => ItemCategory::orderBy('sort', 'DESC')->get(),
            'page'                  => 'pairing',
            'pairing_item_filter'   => Item::whereRelation('tags', 'tag', 'pairing')->orderBy('name')->get()->keyBy('id'),
            'boost_item_filter'     => Item::whereRelation('tags', 'tag', 'boost')->orderBy('name')->get()->keyBy('id'),
        ]);
    }

    /**
     * Create a new pairing.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function createPairings(Request $request, PairingManager $service) {
        $data = $request->only(['character_codes', 'stack_id', 'stack_quantity']);
        if (!$service->createPairing($data, Auth::user())) {
            foreach ($service->errors()->getMessages()['error'] as $error) {
                flash($error)->error();
            }
        } else {
            flash('Pairing created successfully!')->success();
        }

        return redirect()->back();
    }

    /**
     * Approves a pairing request.
     *
     * @param mixed $id
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function cancelPairing(PairingManager $service, $id) {
        if (!$service->cancelPairing(Pairing::findOrFail($id), Auth::user())) {
            foreach ($service->errors()->getMessages()['error'] as $error) {
                flash($error)->error();
            }
        } else {
            flash('Pairing cancelled successfully!')->success();
        }

        return redirect()->back();
    }

    /**
     * Approves a pairing request.
     *
     * @param mixed $id
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function approvePairing(PairingManager $service, $id) {
        if (!$service->approvePairing(Pairing::findOrFail($id))) {
            foreach ($service->errors()->getMessages()['error'] as $error) {
                flash($error)->error();
            }
        } else {
            flash('Pairing approved successfully!')->success();
        }

        return redirect()->back();
    }

    /**
     * Rejects a pairing request.
     *
     * @param mixed $id
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function rejectPairing(PairingManager $service, $id) {
        if (!$service->rejectPairing(Pairing::findOrFail($id), Auth::user())) {
            foreach ($service->errors()->getMessages()['error'] as $error) {
                flash($error)->error();
            }
        } else {
            flash('Pairing rejected successfully!')->success();
        }

        return redirect()->back();
    }

    /**
     * Creates a MYO from the pairing.
     *
     * @param mixed $id
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function createMyos(PairingManager $service, $id) {
        if (!$myos = $service->createMyos(Pairing::findOrFail($id), Auth::user())) {
            foreach ($service->errors()->getMessages()['error'] as $error) {
                flash($error)->error();
            }
        } else {
            flash('Congrats! '.$myos.' Pairing MYO Slots have been created!')->success();
        }

        return redirect()->back();
    }
}
