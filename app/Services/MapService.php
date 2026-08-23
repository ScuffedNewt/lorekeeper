<?php

namespace App\Services;

use App\Models\Map\Map;
use App\Models\Map\MapLocation;
use DB;

class MapService extends Service {
    /*
    |--------------------------------------------------------------------------
    | Map Service
    |--------------------------------------------------------------------------
    |
    | Handles the creation and editing of map categories and maps.
    |
    */

    /**********************************************************************************************

        MAPS

    **********************************************************************************************/

    /**
     * Creates a new map.
     *
     * @param array                 $data
     * @param \App\Models\User\User $user
     *
     * @return bool|Map
     */
    public function createMap($data, $user) {
        DB::beginTransaction();

        try {
            $image = null;
            if (isset($data['image']) && $data['image']) {
                $image = $data['image'];
                unset($data['image']);
            } else {
                $data['has_image'] = 0;
            }

            $map = Map::create($data);

            if ($image) {
                $this->handleImage($image, $map->imagePath, $map->imageFileName);
            }

            return $this->commitReturn($map);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }

    /**
     * Updates an map.
     *
     * @param Map                   $map
     * @param array                 $data
     * @param \App\Models\User\User $user
     *
     * @return bool|Map
     */
    public function updateMap($map, $data, $user) {
        DB::beginTransaction();

        try {
            if (isset($data['map_category_id']) && $data['map_category_id'] == 'none') {
                $data['map_category_id'] = null;
            }

            // More specific validation
            if (Map::where('name', $data['name'])->where('id', '!=', $map->id)->exists()) {
                throw new \Exception('The name has already been taken.');
            }

            $image = null;
            if (isset($data['image']) && $data['image']) {
                $image = $data['image'];
                unset($data['image']);
            }

            $map->update($data);

            if ($map) {
                $this->handleImage($image, $map->imagePath, $map->imageFileName);
            }

            return $this->commitReturn($map);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }

    /**
     * Deletes an map.
     *
     * @param Map $map
     *
     * @return bool
     */
    public function deleteMap($map) {
        DB::beginTransaction();

        try {
            $map->locations()->delete();
            if (file_exists($map->imagePath.'/'.$map->imageFileName)) {
                $this->deleteImage($map->imagePath, $map->imageFileName);
            }
            $map->delete();

            return $this->commitReturn(true);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }

    /**********************************************************************************************

        MAP LOCATIONS

    **********************************************************************************************/

    /**
     * Adds an map location to an map.
     *
     * @param Map   $map
     * @param mixed $data
     *
     * @return bool|string
     */
    public function createLocation($map, $data) {
        DB::beginTransaction();

        try {
            if (!$map) {
                throw new \Exception('Invalid map selected.');
            }

            $location = MapLocation::create([
                'map_id'      => $map->id,
                'name'        => $data['name'],
                'description' => $data['description'],
                'cords'       => $data['cords'],
                'link'        => $data['link'] ?? null,
                'link_type'   => $data['link_type'],
                'is_active'   => isset($data['is_active']),
            ]);

            return $this->commitReturn($location);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }

    /**
     * Edits the data associated with an map location on an map.
     *
     * @param string $location
     * @param array  $data
     *
     * @return bool|string
     */
    public function editLocation($location, $data) {
        DB::beginTransaction();

        try {
            $location->update($data);
            $location->save();

            return $this->commitReturn($location);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }

    /**
     * Removes an map location from an map.
     *
     * @param string $location
     *
     * @return bool|string
     */
    public function deleteLocation($location) {
        DB::beginTransaction();

        try {
            $location->delete();

            return $this->commitReturn(true);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }
}
