<?php namespace App\Http\Controllers\Games;

use Illuminate\Http\Request;

use Auth;
use App\Models\User\GardenPlot;
use App\Models\User\UserGardenPlot;
use App\Models\Item\Item;

class GardenController extends Controller
{
    /**
     * return index
     */
    public function getIndex()
    {
        $seedId = ItemTag::where('tag', 'seeds')->where('is_active', 1); // Removed get()
        $itemIds = $seedId->pluck('item_id'); // Could be combined with above
        $check = UserItem::with('item')->whereIn('item_id', $itemIds)->where('user_id', auth::user()->id)->where('count', '>', 0)->get()->pluck('item.name', 'id');

        return view('gardens.index', [
            'userPlots' => $check,
        ]);
    }
}
