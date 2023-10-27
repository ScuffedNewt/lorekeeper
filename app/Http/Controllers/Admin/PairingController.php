<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Character\Character;
use App\Models\Item\Item;
use App\Models\Item\ItemTag;
use App\Models\Pairing\Pairing;
use App\Services\PairingManager;
use Auth;
use Illuminate\Http\Request;

class PairingController extends Controller {
    /**
     * Shows the pairing roller.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getRoller() {
        $characters = Character::visible()->myo(0)->orderBy('number', 'DESC')->get()->pluck('fullName', 'slug')->toArray();
        $pairingItemIds = ItemTag::where('tag', 'pairing')->pluck('item_id');
        $boostItemIds = ItemTag::where('tag', 'boost')->pluck('item_id');

        return view('admin.pairings.roller', [
            'characters'     => $characters,
            'boost_items'    => Item::whereIn('id', $boostItemIds)->pluck('name', 'id'),
            'pairing_items'  => Item::whereIn('id', $pairingItemIds)->pluck('name', 'id'),
        ]);
    }

    /**
     * Does a test roll.
     *
     * @param App\Services\RaffleService $service
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postRoll(Request $request, PairingManager $service) {
        $characters = Character::visible()->myo(0)->orderBy('number', 'DESC')->get()->pluck('fullName', 'slug')->toArray();
        $pairingItemIds = ItemTag::where('tag', 'pairing')->pluck('item_id');
        $boostItemIds = ItemTag::where('tag', 'boost')->pluck('item_id');

        $data = $request->only(['character_codes', 'pairing_item_id', 'boost_item_ids']);
        if (!$testMyos = $service->rollTestMyos($data, Auth::user())) {
            return "<div class='alert alert-danger'>".$service->errors()->getMessages()['error'][0].'</div>';
        } else {
            return view('admin.pairings._roller_myos', [
                'boost_items'        => Item::whereIn('id', $boostItemIds)->pluck('name', 'id'),
                'pairing_items'      => Item::whereIn('id', $pairingItemIds)->pluck('name', 'id'),
                'testMyos'           => $testMyos,
                'pairing_characters' => $data['character_codes'],
                'characters'         => $characters,
                'pairing_item_id'    => $data['pairing_item_id'],
                'boost_item_ids'     => $data['boost_item_ids'],
            ]);
        }
    }
}
