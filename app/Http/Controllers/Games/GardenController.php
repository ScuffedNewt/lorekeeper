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
        foreach($seedId->get() as $seed)
        {   
            if($seed->data['start_month'] && $seed->data['end_month'] && ($seed->data['start_month'] > Carbon::now()->month || $seed->data['end_month'] < Carbon::now()->month)) {
                // remove seed from array
                $seedId->where('id', '!=', $seed->id);
            }
        }
        $checkSeeds = Auth::user()->items()->whereIn('item_id', $seedId->pluck('item_id'))->where('count', '>', 0)->get()->groupBy(['item_id', 'id']);

        // coops & barns
        $coopId = ItemTag::where('tag', 'coop')->where('is_active', 1); 
        $barnId = ItemTag::where('tag', 'barn')->where('is_active', 1); 

        $checkCoops = Auth::user()->items()->whereIn('item_id', $coopId->pluck('item_id'))->where('count', '>', 0)->get()->groupBy(['item_id', 'id']);
        $checkBarns = Auth::user()->items()->whereIn('item_id', $barnId->pluck('item_id'))->where('count', '>', 0)->get()->groupBy(['item_id', 'id']);
        //

        //getting users modifer items
        $ModifiersId = ItemTag::where('tag', 'fertiliser')->where('is_active', 1); // Removed get()
        $checkModifiers = Auth::user()->items()->whereIn('item_id', $ModifiersId->pluck('item_id'))->where('count', '>', 0)->get()->groupBy(['item_id', 'id']);

        $this->checkPlots(Auth::user());
        return view('garden.index', [
            'userSeeds' => $checkSeeds,
            'userCoops' => $checkCoops,
            'userBarns' => $checkBarns,
            'userModifiers' => $checkModifiers,
            'plots' => GardenPlot::all(),
        ]);
    }

    /**
     * Plant seed
     */
    public function postPlantSeed(Request $request)
    {
        $data = $request->only(['seedID', 'coopID', 'barnID', 'plotID', 'type']);
        $service = new PlotManager;
        switch($data['type']) {
            case 'seed':
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
            break;
            case 'coop': case'barn':
                if($service->place(Auth::user(), $data)) {
            
                    flash('Placed successfully!')->success();
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
            break;
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
     * Water plot | feed animal
     */
    public function postWaterPlot(Request $request)
    {
        $data = $request->input('plotID');
        $service = new PlotManager;
        if($service->water(Auth::user(), $data)) {
            if($plot->plot->type == 'seed')
            flash('Watered successfully!')->success();
            else 
            flash('Fed successfully!')->success();
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
     * when the feeding | waterings have met the requirements and the user gets their reward
     */
    public function postClearPlot(Request $request)
    {
        $data = $request->input(['plotID', 'type']);
        $service = new PlotManager;
        switch($data['type']) {
            case 'seed':
                if($service->claim(Auth::user(), $data)) {
            
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
            break;
            case 'coop': case'barn':
                if($service->animalClaim(Auth::user(), $data)) {
            
                    flash('Placed successfully!')->success();
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
            break;
        }
    }

    /**
     * Claim thing
     */
    public function postClaim($id, PlotManager $service)
    {
        if($service->claim(Auth::user(), $id)) {
            flash('Claimed successfully!')->success();
        }
        else {
            foreach($service->errors()->getMessages()['error'] as $error) flash($error)->error();
        }
        return redirect()->back();
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

    /**
     * Get skull modal
     */
    public function getSkull($id)
    {
        $plot = UserGardenPlot::find($id);
        if(!$plot) abort(404);
        if($plot->type == 'seed') {
            return view('garden._skull_modal', [
                'plot' => $plot
            ]);
        }
        else {
            return view('garden._clear_barn_coop', [
                'plot' => $plot
            ]);
        }
    }

    /**
     * 
     */
    public function destroyCrop($id, PlotManager $service)
    {
        if($service->clear(Auth::user(), $id)) {
            $plot = UserGardenPlot::find($id);
            if($plot->plot->type == 'seed') {
                flash('Crop destroyed successfully!')->success();
            }
            else {
                flash('Removed successfully!')->success();
            }
        }
        else {
            foreach($service->errors()->getMessages()['error'] as $error) flash($error)->error();
        }
        return redirect()->back();
    }
}
