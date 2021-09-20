<?php

namespace App\Http\Controllers\Admin\Data;

use Illuminate\Http\Request;

use Auth;

use App\Models\User\GardenPlot;
use App\Models\Loot\LootTable;
use App\Models\Raffle\Raffle;
use App\Models\Currency\Currency;

use App\Models\Garden\UserGardenPlot;

use App\Services\PlotService;

use App\Http\Controllers\Controller;

class PlotController extends Controller
{

    public function getIndex()
    {
        return view('admin.plots.index', [
            'plots' => GardenPlot::all(),
        ]);
    }

    public function getCreatePlot()
    {
        return view('admin.plots.create_edit_plot', [
            'plot' => new GardenPlot,
            'currencies' => Currency::where('is_user_owned', 1)->orderBy('sort_user', 'DESC')->pluck('name', 'id'),
        ]);
    }

        /**
     * Shows the edit plot page.
     *
     * @param  int  $id
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getEditPlot($id)
    {
        $plot = GardenPlot::find($id);
        if(!$plot) abort(404);
        return view('admin.plots.create_edit_plot', [
            'plot' => $plot,
            'currencies' => Currency::where('is_user_owned', 1)->orderBy('sort_user', 'DESC')->pluck('name', 'id'),
        ]);
    }

        /**
     * Creates or edits an plot.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  App\Services\PlotService  $service
     * @param  int|null                  $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postCreateEditPlot(Request $request, PlotService $service, $id = null)
    {
        $data = $request->only(['free', 'currency_id', 'plot_cost', 'plot_type']);
        if($id && $service->updatePlot(GardenPlot::find($id), $data, Auth::user())) {
            flash('Plot updated successfully.')->success();
        }
        else if (!$id && $plot = $service->createPlot($data, Auth::user())) {
            flash('Plot created successfully.')->success();
            return redirect()->to('admin/data/plots/edit/'.$plot->id);
        }
        else {
            foreach($service->errors()->getMessages()['error'] as $error) flash($error)->error();
        }
        return redirect()->back();
    }

    /**
     * Gets the plot deletion modal.
     *
     * @param  int  $id
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getDeletePlot($id)
    {
        $plot = GardenPlot::find($id);
        return view('admin.plots._delete_plot', [
            'plot' => $plot,
        ]);
    }

    /**
     * Creates or edits an plot.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  App\Services\PlotService  $service
     * @param  int                       $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postDeletePlot(Request $request, PlotService $service, $id)
    {
        if($id && $service->deletePlot(GardenPlot::find($id))) {
            flash('Plot deleted successfully.')->success();
        }
        else {
            foreach($service->errors()->getMessages()['error'] as $error) flash($error)->error();
        }
        return redirect()->to('admin/data/plots');
    }
}