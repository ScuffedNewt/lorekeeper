<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Garden\Garden;
use App\Models\Garden\GardenObject;
use App\Models\Garden\GardenPlot;
use App\Models\Item\Item;
use App\Services\Garden\GardenService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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
            'name', 'description', 'image', 'remove_image', 'background_image', 'remove_background_image', 'plot_image', 'remove_plot_image',
            'can_perish', 'is_active', 'allowed_object_types',
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
     * @param int                               $id
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postDeleteGarden(Request $request, GardenService $service, $id) {
        if ($id && $service->deleteGarden(Garden::find($id), Auth::user())) {
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
     * @param mixed $id
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getCreateGardenPlot($id) {
        $garden = Garden::find($id);
        if (!$garden) {
            abort(404);
        }

        return view('admin.gardens._create_garden_plot', [
            'plot'   => new GardenPlot,
            'garden' => $garden,
        ]);
    }

    /**
     * Gets the edit garden plot modal.
     *
     * @param mixed $gardenId
     * @param mixed $plotId
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getEditGardenPlot($gardenId, $plotId) {
        $plot = GardenPlot::find($plotId);
        if (!$plot) {
            abort(404);
        }

        return view('admin.gardens.edit_garden_plot', [
            'plot'   => $plot,
            'garden' => $plot->garden,
        ]);
    }

    /**
     * Creates or edits a garden plot.
     *
     * @param App\Services\Garden\GardenService $service
     * @param int                               $id
     * @param mixed                             $gardenId
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postCreateEditGardenPlot(Request $request, GardenService $service, $gardenId, $id = null) {
        $data = $request->only([
            'allowed_object_types', 'can_perish', 'ignore_seasons', 'image', 'remove_image', 'name',
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
     * @param mixed $gardenId
     * @param mixed $plotId
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getDeleteGardenPlot($gardenId, $plotId) {
        $plot = GardenPlot::find($plotId);
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
     * @param int                               $id
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postDeleteGardenPlot(Request $request, GardenService $service, $id) {
        $plot = GardenPlot::find($id);
        $garden = $plot->garden;
        if ($id && $service->deleteGardenPlot($plot)) {
            flash('Garden plot deleted successfully.')->success();
        } else {
            foreach ($service->errors()->getMessages()['error'] as $error) {
                flash($error)->error();
            }
        }

        return redirect()->to('admin/gardens/edit/'.$garden->id);
    }

    /**********************************************************************************************

        GARDEN OBJECTS

    **********************************************************************************************/

    /**
     * Shows the garden objects index page.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getGardenObjectsIndex(Request $request) {
        $query = GardenObject::query();
        $data = $request->only(['name', 'type']);
        if (isset($data['name'])) {
            $query->where('name', 'like', '%'.$data['name'].'%');
        }
        if (isset($data['type'])) {
            $query->where('type', $data['type']);
        }

        return view('admin.gardens.objects.index', [
            'objects' => GardenObject::all(),
        ]);
    }

    /**
     * Shows the create garden object page.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getCreateGardenObject() {
        return view('admin.gardens.objects.create_edit_garden_object', [
            'object'        => new GardenObject,
            'object_types'  => getGardenObjectTypes(),
            'object_models' => [
                // if you want to add more object types, add them here
                get_class(new Item) => 'Item',
            ],
            'objects' => [null => 'Select Object Type'],
        ]);
    }

    /**
     * Shows the edit garden object page.
     *
     * @param int $id
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getEditGardenObject($id) {
        $object = GardenObject::find($id);
        if (!$object) {
            abort(404);
        }

        return view('admin.gardens.objects.create_edit_garden_object', [
            'object'        => $object,
            'object_types'  => getGardenObjectTypes(),
            'object_models' => [
                // if you want to add more object types, add them here
                get_class(new Item) => 'Item',
            ],
            'objects' => $object->object_model::all()->pluck('name', 'id'),
        ]);
    }

    /**
     * Creates or edits a garden object.
     *
     * @param App\Services\Garden\GardenService $service
     * @param int                               $id
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postCreateEditGardenObject(Request $request, GardenService $service, $id = null) {
        $data = $request->all();
        if ($id && $service->editGardenObject(GardenObject::find($id), $data, Auth::user())) {
            flash('Garden object updated successfully.')->success();
        } elseif (!$id && $object = $service->createGardenObject($data, Auth::user())) {
            flash('Garden object created successfully.')->success();

            return redirect()->to('admin/gardens/objects/edit/'.$object->id);
        } else {
            foreach ($service->errors()->getMessages()['error'] as $error) {
                flash($error)->error();
            }
        }

        return redirect()->back();
    }

    /**
     * Gets the garden object deletion modal.
     *
     * @param int $id
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getDeleteGardenObject($id) {
        return view('admin.gardens.objects._delete_garden_object', ['object' => GardenObject::findOrFail($id)]);
    }

    /**
     * Deletes a garden object.
     *
     * @param App\Services\Garden\GardenService $service
     * @param int                               $id
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postDeleteGardenObject(GardenService $service, $id) {
        if ($service->deleteGardenObject(GardenObject::find($id), Auth::user())) {
            flash('Garden object deleted successfully.')->success();

            return redirect()->to('admin/gardens/objects');
        }
        foreach ($service->errors()->getMessages()['error'] as $error) {
            flash($error)->error();
        }

        return redirect()->back();
    }

    /**
     * Gets all of the specified object type's options.
     *
     * @param string $type
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getTypeOptions($type) {
        if (view()->exists('admin.gardens.objects.types.'.$type)) {
            return view('admin.gardens.objects.types.'.$type, [
                //
            ])->render();
        }

        return '';
    }

    /**
     * Returns all of the specified object type.
     *
     * @param string $type
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getObjects($type) {
        $type = urldecode($type);
        switch ($type) {
            case get_class(new Item):
                $objects = Item::all()->pluck('name', 'id');
                break;
            default:
                $objects = [null => 'Select Object Type'];
        }

        return view('admin.gardens.objects._objects', [
            'objects' => $objects,
        ])->render();
    }
}
