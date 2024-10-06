<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Garden\Garden;
use App\Models\Garden\GardenPlot;
use App\Services\Garden\GardenService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

class GardenController extends Controller {

    /**********************************************************************************************

        GARDENS

    **********************************************************************************************/

    /**
     * Shows the garden index page.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getGardenIndex() {
        return view('admin.gardens.index', [
            'gardens' => Garden::all(),
        ]);
    }

    /**
     * Shows the create garden page.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getCreateGarden() {
        return view('admin.gardens.create_edit_garden', [
            'garden' => new Garden,
        ]);
    }

    /**
     * Shows the edit garden page.
     *
     * @param int $id
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getEditGarden($id) {
        $garden = Garden::find($id);
        if (!$garden) {
            abort(404);
        }

        return view('admin.gardens.create_edit_garden', [
            'garden' => $garden,
        ]);
    }

    /**
     * Creates or edits a garden.
     *
     * @param App\Services\Garden\GardenService $service
     * @param int|null                          $id
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postCreateEditGarden(Request $request, GardenService $service, $id = null) {
        $data = $request->only([
            'name', 'description', 'image', 'remove_image', 'background_image', 'remove_background_image', 'garden_image', 'remove_garden_image',
            'can_perish', 'is_active',
        ]);
        if ($id && $service->updateGarden(Garden::find($id), $data, Auth::user())) {
            flash('Garden updated successfully.')->success();
        } elseif (!$id && $garden = $service->createGarden($data, Auth::user())) {
            flash('Garden created successfully.')->success();

            return redirect()->to('admin/gardens/edit/'.$garden->id);
        } else {
            foreach ($service->errors()->getMessages()['error'] as $error) {
                flash($error)->error();
            }
        }

        return redirect()->back();
    }

    /**
     * Gets the garden deletion modal.
     *
     * @param int $id
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getDeleteGarden($id) {
        $garden = Garden::find($id);
        if (!$garden) {
            abort(404);
        }

        return view('admin.gardens._delete_garden', [
            'garden' => $garden,
        ]);
    }

    /**
     * Deletes a garden.
     *
     * @param App\Services\Garden\GardenService $service
     * @param int                      $id
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postDeleteGarden(Request $request, GardenService $service, $id) {
        if ($id && $service->deleteGarden(Garden::find($id))) {
            flash('Garden deleted successfully.')->success();
        } else {
            foreach ($service->errors()->getMessages()['error'] as $error) {
                flash($error)->error();
            }
        }

        return redirect()->to('admin/gardens');
    }

    /**********************************************************************************************

        GARDEN PLOTS

    **********************************************************************************************/

    /**
     * Gets the create garden plot modal.
     * 
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getCreateGardenPlot($id) {
        $garden = Garden::find($id);
        if (!$garden) {
            abort(404);
        }

        return view('admin.gardens._create_edit_garden_plot', [
            'plot' => new GardenPlot,
            'garden' => $garden,
        ]);
    }

    /**
     * Gets the edit garden plot modal.
     *
     * @param int $id
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getEditGardenPlot($id) {
        $plot = GardenPlot::find($id);
        if (!$plot) {
            abort(404);
        }

        return view('admin.gardens.edit_garden_plot', [
            'plot' => $plot,
            'garden' => $plot->garden,
        ]);
    }

    /**
     * Creates or edits a garden plot.
     *
     * @param App\Services\Garden\GardenService $service
     * @param int                      $id
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postCreateEditGardenPlot(Request $request, GardenService $service, $gardenId, $id = null) {
        $data = $request->only([
            'data',
        ]);
        if ($id && $service->updateGardenPlot(GardenPlot::find($id), $data, Auth::user())) {
            flash('Garden plot updated successfully.')->success();
        } elseif (!$id && $plot = $service->createGardenPlot(Garden::find($gardenId))) {
            flash('Garden plot created successfully.')->success();

            return redirect()->to('admin/gardens/edit/'.$plot->garden_id);
        } else {
            foreach ($service->errors()->getMessages()['error'] as $error) {
                flash($error)->error();
            }
        }

        return redirect()->back();
    }

    /**
     * Gets the garden plot deletion modal.
     *
     * @param int $id
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getDeleteGardenPlot($id) {
        $plot = GardenPlot::find($id);
        if (!$plot) {
            abort(404);
        }

        return view('admin.gardens._delete_garden_plot', [
            'plot' => $plot,
        ]);
    }

    /**
     * Deletes a garden plot.
     *
     * @param App\Services\Garden\GardenService $service
     * @param int                      $id
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postDeleteGardenPlot(Request $request, GardenService $service, $id) {
        if ($id && $service->deleteGardenPlot(GardenPlot::find($id))) {
            flash('Garden plot deleted successfully.')->success();
        } else {
            foreach ($service->errors()->getMessages()['error'] as $error) {
                flash($error)->error();
            }
        }

        return redirect()->to('admin/gardens');
    }
}
