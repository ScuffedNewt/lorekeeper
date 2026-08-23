<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Map\Map;
use App\Models\Map\MapLocation;
use App\Services\MapService;
use Auth;
use Illuminate\Http\Request;

class MapController extends Controller {
    /**
     * Get index.
     */
    public function getMapIndex() {
        return view('admin.maps.maps', [
            'maps' => Map::all(),
        ]);
    }

    /**
     * Shows the create map page.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getCreateMap() {
        return view('admin.maps.create_edit_map', [
            'map' => new Map,
        ]);
    }

    /**
     * Shows the edit map page.
     *
     * @param mixed $id
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getEditMap($id) {
        return view('admin.maps.create_edit_map', [
            'map' => Map::findOrFail($id),
        ]);
    }

    /**
     * Creates or edits an map.
     *
     * @param App\Services\MapService $service
     * @param int|null                $id
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postCreateEditMap(Request $request, MapService $service, $id = null) {
        $id ? $request->validate(Map::$updateRules) : $request->validate(Map::$createRules);
        $data = $request->only([
            'name', 'description', 'image', 'is_active',
        ]);
        if ($id && $service->updateMap(Map::find($id), $data, Auth::user())) {
            flash('Map updated successfully.')->success();
        } elseif (!$id && $map = $service->createMap($data, Auth::user())) {
            flash('Map created successfully.')->success();

            return redirect()->to('admin/maps/edit/'.$map->id);
        } else {
            foreach ($service->errors()->getMessages()['error'] as $error) {
                flash($error)->error();
            }
        }

        return redirect()->back();
    }

    /**
     * Deletes a map.
     *
     * @param mixed $id
     */
    public function getDeleteMap($id) {
        $map = Map::findOrFail($id);

        return view('admin.maps._delete_map', [
            'map' => $map,
        ]);
    }

    /**
     * Deletes a map.
     *
     * @param mixed $id
     */
    public function postDeleteMap(Request $request, MapService $service, $id) {
        if ($service->deleteMap(Map::find($id))) {
            flash('Map deleted successfully.')->success();
        } else {
            foreach ($service->errors()->getMessages()['error'] as $error) {
                flash($error)->error();
            }
        }

        return redirect()->to('admin/maps');
    }

    /**********************************************************************************************

        MAP LOCATIONS

    **********************************************************************************************/

    /**
     * get create.
     *
     * @param mixed $map_id
     */
    public function getCreateLocation($map_id) {
        return view('admin.maps._create_edit_location', [
            'map'      => Map::findOrFail($map_id),
            'location' => new MapLocation,
        ]);
    }

    /**
     * get edit.
     *
     * @param mixed $id
     */
    public function getEditLocation($id) {
        $location = MapLocation::findOrFail($id);

        return view('admin.maps._create_edit_location', [
            'location' => $location,
            'map'      => $location->map,
        ]);
    }

    /**
     * post create / edit.
     *
     * @param mixed|null $map_id
     * @param mixed|null $id
     */
    public function postCreateEditLocation(Request $request, MapService $service, $map_id = null, $id = null) {
        $data = $request->only([
            'name', 'description', 'cords', 'link', 'link_type', 'is_active',
        ]);
        if ($id && $service->editLocation(MapLocation::find($id), $data)) {
            flash('Location updated successfully.')->success();
        } elseif (!$id && $location = $service->createLocation(Map::find($map_id), $data)) {
            flash('Location created successfully.')->success();
        } else {
            foreach ($service->errors()->getMessages()['error'] as $error) {
                flash($error)->error();
            }
        }

        return redirect()->back();
    }

    /**
     * get delete.
     *
     * @param mixed $id
     */
    public function getDeleteLocation($id) {
        $location = MapLocation::findOrFail($id);

        return view('admin.maps._delete_location', [
            'location' => $location,
            'map'      => $location->map,
        ]);
    }

    /**
     * post delete.
     *
     * @param mixed $id
     */
    public function postDeleteLocation(Request $request, MapService $service, $id) {
        $location = MapLocation::findOrFail($id);
        $map = $location->map;
        if ($service->deleteLocation($location)) {
            flash('Location deleted successfully.')->success();
        } else {
            foreach ($service->errors()->getMessages()['error'] as $error) {
                flash($error)->error();
            }
        }

        return redirect('admin/maps/edit/'.$map->id);
    }
}
