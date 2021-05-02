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
        //getting users seeds items
        $seedId = ItemTag::where('tag', 'seed')->where('is_active', 1); // Removed get()
        $checkSeeds = Auth::user()->items()->whereIn('item_id', $seedId->pluck('item_id'))->where('count', '>', 0)->get()->groupBy(['item_id', 'id']);

        //getting users modifer items
        $ModifiersId = ItemTag::where('tag', 'fertiliser')->where('is_active', 1); // Removed get()
        $checkModifiers = Auth::user()->items()->whereIn('item_id', $ModifiersId->pluck('item_id'))->where('count', '>', 0)->get()->groupBy(['item_id', 'id']);

        return view('garden.index', [
            'userSeeds' => $checkSeeds,
            'userModifiers' => $checkModifiers,
            'plots' => GardenPlot::all(),
        ]);
    }

    /**
     * plant seed
     */
    public function getPlantSeed(Request $request)
    {
        $data = $request->only(['seedID', 'plotID']);
        $service = new PlotManager;
        if($service->plant(Auth::user(), $data)) {
            flash('Planted successfully!')->success();
        }
        else {
            foreach($service->errors()->getMessages()['error'] as $error) flash($error)->error();
        }
        return redirect()->back();
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
}
