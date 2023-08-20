<?php namespace App\Http\Controllers\Admin;

use Auth;
use DB;
use Exception;
use Settings;

use Carbon\Carbon;
use Illuminate\Http\Request;

use App\Models\User\User;
use App\Models\Character\Character;
use App\Models\Item\Item;
use App\Models\Item\ItemTag;
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

        $itemIds = ItemTag::where('tag', 'pairing')->pluck('item_id');
        $items = Item::whereIn('id', $itemIds)->orderBy('name')->pluck('name', 'id');

        return view('admin.pairings.roller', [
            'items' => $items,
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
        $character_1_code =  $request->character_1_code;
        $character_2_code =  $request->character_2_code;
        $item_id = $request->item_id;

        $itemIds = ItemTag::where('tag', 'pairing')->pluck('item_id');
        $items = Item::whereIn('id', $itemIds)->orderBy('name')->pluck('name', 'id');

        //validations just to be sure, these are the same as in the pairingmanager
        if($service->validatePairingBasics($character_1_code, $character_2_code, $item_id)){
            $user = Auth::user();
            $testMyos = $service->rollTestMyos($character_1_code, $character_2_code,$item_id, $user);
    
            if (isset($testMyos)) {
                return view('admin.pairings.roller', [
                    'items' => $items,
                    'testMyos' => $testMyos,
                    'slug1' => $character_1_code,
                    'slug2' => $character_2_code,
                ]);
            }
        } else {
            foreach($service->errors()->getMessages()['error'] as $error) flash($error)->error();
        }  
        return redirect()->back();
   
    }

}
