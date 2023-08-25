<?php namespace App\Http\Controllers\Admin;

use Auth;
use DB;
use Exception;
use Settings;

use Carbon\Carbon;
use Illuminate\Http\Request;

use App\Models\User\User;
use App\Models\User\UserItem;

use App\Models\Character\Character;
use App\Models\Item\Item;
use App\Models\Item\ItemTag;
use App\Models\Item\ItemCategory;

use App\Models\Character\Sublist;
use App\Http\Controllers\Controller;

use App\Models\Pairing\Pairing;
use App\Services\PairingManager;


class PairingController extends Controller
{
    /**
     * Shows the pairing roller.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getRoller(Request $request)
    {

        $pairingItemIds = ItemTag::where('tag', 'pairing')->pluck('item_id');
        $boostItemIds = ItemTag::where('tag', 'boost')->pluck('item_id');
    
        return view('admin.pairings.roller', [
            'inventory' => Item::whereIn('id', $boostItemIds)->orWhereIn('id', $pairingItemIds)->pluck('name', 'id'),
        ]);
    }

    /**
     * Does a test roll.
     *
     * @param  \Illuminate\Http\Request    $request
     * @param  App\Services\RaffleService  $service
     * @param  int|null                    $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postRoll(Request $request, PairingManager $service)
    {

        $pairingItemIds = ItemTag::where('tag', 'pairing')->pluck('item_id');
        $boostItemIds = ItemTag::where('tag', 'boost')->pluck('item_id');

        $character1Code =  $request->character_1_code;
        $character2Code =  $request->character_2_code;
        $itemIds = $request->item_id;

        $user = Auth::user();
        $testMyos = $service->rollTestMyos($character1Code, $character2Code,$itemIds, $user);
    
        if (isset($testMyos)) {
            return view('admin.pairings.roller', [
                'items' => $itemIds,
                'inventory' => Item::whereIn('id', $boostItemIds)->orWhereIn('id', $pairingItemIds)->pluck('name', 'id'),
                'testMyos' => $testMyos,
                'slug1' => $character1Code,
                'slug2' => $character2Code,
                'item_ids' => array_filter($itemIds)
            ]);
        } else {
            foreach($service->errors()->getMessages()['error'] as $error) flash($error)->error();
        }
      
        return redirect()->back();
   
    }

}
