<?php namespace App\Http\Controllers\Games;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

use Auth;
use Carbon\Carbon; 

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

        $this->checkPlots(Auth::user());

        return view('garden.index', [
            'userSeeds' => $checkSeeds,
            'userModifiers' => $checkModifiers,
            'plots' => GardenPlot::all(),
        ]);
    }

    /**
     * Plant seed
     */
    public function postPlantSeed(Request $request)
    {
        $data = $request->only(['seedID', 'plotID']);
        $service = new PlotManager;
        if($service->plant(Auth::user(), $data)) {
            
            flash('Planted successfully!')->success();
            $response = array(
                'status' => 'success',
                'msg' => $request->message,
            );
            return response()->json($response); 
        }
        else {
            foreach($service->errors()->getMessages()['error'] as $error) 
            flash($error)->error();
        }
    }

    /**
     * Add Modifier
     */
    public function postAddMod(Request $request)
    {
        $data = $request->only(['modID', 'plotID']);
        $service = new PlotManager;
        if($service->mod(Auth::user(), $data)) {
            
            flash('Added modifier successfully!')->success();
            $response = array(
                'status' => 'success',
                'msg' => $request->message,
            );
            return response()->json($response); 
        }
        else {
            foreach($service->errors()->getMessages()['error'] as $error) 
            flash($error)->error();
        }
    }

    /**
     * Water plot
     */
    public function postWaterPlot(Request $request)
    {
        $data = $request->input('plotID');
        $service = new PlotManager;
        if($service->water(Auth::user(), $data)) {
            flash('Watered successfully!')->success();
            $response = array(
                'status' => 'success',
                'msg' => $request->message,
            );
            return response()->json($response); 
        }
        else {
            foreach($service->errors()->getMessages()['error'] as $error) flash($error)->error();
        }
    }

    /**
     * 
     */
    public function postClearPlot(Request $request)
    {
        $data = $request->input(['plotID']);
        $service = new PlotManager;
        if($service->clear(Auth::user(), $data)) {
            
            flash('Cleared successfully!')->success();
            $response = array(
                'status' => 'success',
                'msg' => $request->message,
            );
            return response()->json($response); 
        }
        else {
            foreach($service->errors()->getMessages()['error'] as $error) flash($error)->error();
        }
    }

    public function postClaim()
    {
        ////
    }
    
    /******************************************************************************************************
     *  PLOTS
     ******************************************************************************************************/

     /**
      * Buys the plots
      */
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

    /**
     * Checks if any plants have died since the user has last logged on
     */
    public function checkPlots($user)
    {
        $check = 0;
        foreach($user->gardenplots as $plot)
        {
            $water = Carbon::parse($plot->water_at)->addHours($plot->time);
            if($water < Carbon::now())
            {
                if($plot->is_dead != 1)
                {
                    $plot->is_dead = 1;
                    $plot->save();

                    $check = 1;
                }
            }
        }
        if($check == 1) flash('One or more of your plants has died due to lack of watering.')->error();
    }
}
