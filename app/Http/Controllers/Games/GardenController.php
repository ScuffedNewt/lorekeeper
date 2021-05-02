<?php namespace App\Http\Controllers\Games;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

use Auth;
use App\Models\User\GardenPlot;
use App\Models\User\UserGardenPlot;
use App\Models\Item\Item;
use App\Models\Item\ItemTag;
use App\Models\User\UserItem;

use App\Services\PlotManager;
class GardenController extends Controller
{
    /**
     * return index
     */
    public function getIndex()
    {
        $seedId = ItemTag::where('tag', 'seed')->where('is_active', 1); // Removed get()
        $itemIds = $seedId->pluck('item_id'); // Could be combined with above
        $check = Auth::user()->items()->whereIn('item_id', $itemIds)->where('count', '>', 0)->get()->groupBy(['item_id', 'id']);

        return view('garden.index', [
            'userSeeds' => $check,
            'plots' => GardenPlot::all(),
        ]);
    }

    /******************************************************************************************************
     *  PLOTS
     ******************************************************************************************************/

    public function postPurchasePlot($id, PlotManager $service)
    {
        $plot = GardenPlot::find($id);
        if(!$plot) abort(404);
        if($service->purchasePlot($plot, Auth::user())) {
            flash('Plot purchased successfully.')->success();
        }
        else {
            foreach($service->errors()->getMessages()['error'] as $error) flash($error)->error();
        }
        return redirect()->back();
    }

    public function postPlantSeed(Request $request)
    {
        $data = $request->all();
        console.log("we droppin");
        return response()->json(['success'=>'Hey we planted']);
    }
}
