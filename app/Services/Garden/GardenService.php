<?php

namespace App\Services\Garden;

use App\Models\Garden\Garden;
use App\Models\Garden\GardenObject;
use App\Models\Garden\GardenPlot;
use App\Services\Service;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class GardenService extends Service {
    /*
    |--------------------------------------------------------------------------
    | Garden Service
    |--------------------------------------------------------------------------
    |
    | Handles the creation and editing of garden categories and gardens.
    |
    */

    /**********************************************************************************************

        GARDENS

    **********************************************************************************************/

    /**
     * Creates a new garden.
     *
     * @param array                 $data
     * @param \App\Models\User\User $user
     *
     * @return bool|Garden
     */
    public function createGarden($data, $user) {
        DB::beginTransaction();

        try {
            if (Garden::where('name', $data['name'])->exists()) {
                throw new \Exception('The name has already been taken.');
            }

            $data = $this->populateData($data);

            $image = null;
            if (isset($data['image']) && $data['image']) {
                $data['has_image'] = 1;
                $data['hash'] = randomString(10);
                $image = $data['image'];
                unset($data['image']);
            } else {
                $data['has_image'] = 0;
            }

            $backgroundImage = null;
            if (isset($data['background_image']) && $data['background_image']) {
                $data['has_background_image'] = 1;
                $data['background_hash'] = randomString(10);
                $backgroundImage = $data['background_image'];
                unset($data['background_image']);
            } else {
                $data['has_background_image'] = 0;
            }

            $plotImage = null;
            if (isset($data['plot_image']) && $data['plot_image']) {
                $data['has_plot_image'] = 1;
                $data['plot_hash'] = randomString(10);
                $plotImage = $data['plot_image'];
                unset($data['plot_image']);
            } else {
                $data['has_plot_image'] = 0;
            }

            $garden = Garden::create($data);

            if (!$this->logAdminAction($user, 'Created Garden', 'Created '.$garden->displayName)) {
                throw new \Exception('Failed to log admin action.');
            }

            if ($image) {
                $this->handleImage($image, $garden->imagePath, $garden->imageFileName);
            }
            if ($backgroundImage) {
                $this->handleImage($backgroundImage, $garden->backgroundImagePath, $garden->backgroundImageFileName);
            }
            if ($plotImage) {
                $this->handleImage($plotImage, $garden->plotImagePath, $garden->plotImageFileName);
            }

            return $this->commitReturn($garden);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }

    /**
     * Updates an garden.
     *
     * @param Garden                $garden
     * @param array                 $data
     * @param \App\Models\User\User $user
     *
     * @return bool|Garden
     */
    public function updateGarden($garden, $data, $user) {
        DB::beginTransaction();

        try {
            if (Garden::where('name', $data['name'])->where('id', '!=', $garden->id)->exists()) {
                throw new \Exception('The name has already been taken.');
            }
            $data = $this->populateData($data, $garden);

            $image = null;
            if (isset($data['image']) && $data['image']) {
                $data['has_image'] = 1;
                $data['hash'] = randomString(10);
                $image = $data['image'];
                unset($data['image']);
            }

            $backgroundImage = null;
            if (isset($data['background_image']) && $data['background_image']) {
                $data['has_background_image'] = 1;
                $data['background_hash'] = randomString(10);
                $backgroundImage = $data['background_image'];
                unset($data['background_image']);
            }

            $plotImage = null;
            if (isset($data['plot_image']) && $data['plot_image']) {
                $data['has_plot_image'] = 1;
                $data['plot_hash'] = randomString(10);
                $plotImage = $data['plot_image'];
                unset($data['plot_image']);
            }

            $garden->update($data);

            if (!$this->logAdminAction($user, 'Updated Garden', 'Updated '.$garden->displayName)) {
                throw new \Exception('Failed to log admin action.');
            }

            if ($garden) {
                $this->handleImage($image, $garden->imagePath, $garden->imageFileName);
                $this->handleImage($backgroundImage, $garden->backgroundImagePath, $garden->backgroundImageFileName);
                $this->handleImage($plotImage, $garden->plotImagePath, $garden->plotImageFileName);
            }

            return $this->commitReturn($garden);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }

    /**
     * Deletes an garden.
     *
     * @param Garden $garden
     * @param mixed  $user
     *
     * @return bool
     */
    public function deleteGarden($garden, $user) {
        DB::beginTransaction();

        try {
            // Check first if the garden is currently owned or if some other site feature uses it
            if (DB::table('user_gardens')->where([['garden_id', '=', $garden->id], ['count', '>', 0]])->exists()) {
                throw new \Exception('At least one user currently owns this garden. Please remove the garden(s) before deleting it.');
            }

            if (!$this->logAdminAction($user, 'Deleted Garden', 'Deleted '.$garden->name)) {
                throw new \Exception('Failed to log admin action.');
            }

            $garden->plots()->delete();
            if ($garden->has_image) {
                $this->deleteImage($garden->imagePath, $garden->imageFileName);
            }
            if ($garden->has_background_image) {
                $this->deleteImage($garden->backgroundImagePath, $garden->backgroundImageFileName);
            }
            if ($garden->has_plot_image) {
                $this->deleteImage($garden->plotImagePath, $garden->plotImageFileName);
            }
            $garden->delete();

            return $this->commitReturn(true);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }

    /**********************************************************************************************

        GARDEN PLOTS

    **********************************************************************************************/

    /**
     * Creates a new plot.
     *
     * @param Garden $garden
     *
     * @return bool|GardenPlot
     */
    public function createGardenPlot($garden) {
        DB::beginTransaction();

        try {
            // Plots don't have any data on creation, so we just need to associate it with the garden
            $plot = GardenPlot::create([
                'garden_id' => $garden->id,
            ]);

            return $this->commitReturn($plot);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }

    /**
     * Updates a garden plot's data.
     *
     * @param GardenPlot $plot
     * @param mixed      $data
     *
     * @return bool|GardenPlot
     */
    public function updateGardenPlot($plot, $data) {
        DB::beginTransaction();

        try {
            if (!isset($data['can_use_modifiers'])) {
                $data['can_use_modifiers'] = 0;
            }

            if (isset($data['remove_image'])) {
                if ($plot && $plot->has_image && $data['remove_image']) {
                    $data['has_image'] = 0;
                    $this->deleteImage($plot->imagePath, $plot->imageFileName);
                }
                unset($data['remove_image']);
            }

            $image = null;
            if (isset($data['image']) && $data['image']) {
                $data['has_image'] = 1;
                $data['hash'] = randomString(10);
                $image = $data['image'];
                unset($data['image']);
            }

            $plot->update([
                'data'      => Arr::only($data, ['can_use_modifiers']),
                'has_image' => $data['has_image'],
            ]);

            if ($image) {
                $this->handleImage($image, $plot->imagePath, $plot->imageFileName);
            }

            return $this->commitReturn($plot);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }

    /**********************************************************************************************

        GARDEN OBJECTS

    **********************************************************************************************/

    /**
     * Creates a new garden object.
     *
     * @param array $data
     * @param mixed $user
     *
     * @return bool|GardenObject
     */
    public function createGardenObject($data, $user) {
        DB::beginTransaction();

        try {
            $objectData = $this->populateObjectData($data);

            $object = GardenObject::create([
                'type'         => $data['type'],
                'object_model' => $data['object_model'],
                'object_id'    => $data['object_id'],
                'data'         => $objectData,
            ]);

            if (!$this->logAdminAction($user, 'Created Garden Object', 'Created object #'.$object->id)) {
                throw new \Exception('Failed to log admin action.');
            }

            return $this->commitReturn($object);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }

    /**
     * Edits a new garden object.
     *
     * @param array $data
     * @param mixed $object
     * @param mixed $user
     *
     * @return bool|GardenObject
     */
    public function editGardenObject($object, $data, $user) {
        DB::beginTransaction();

        try {
            $objectData = $this->populateObjectData($data, $object);

            $object->update([
                'type'         => $data['type'],
                'object_model' => $data['object_model'],
                'object_id'    => $data['object_id'],
                'data'         => $objectData,
            ]);

            if (!$this->logAdminAction($user, 'Edited Garden Object', 'Edited object #'.$object->id)) {
                throw new \Exception('Failed to log admin action.');
            }

            return $this->commitReturn($object);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }

    /**********************************************************************************************

        MISC

    **********************************************************************************************/

    /**
     * Processes user input for creating/updating a garden.
     *
     * @param array  $data
     * @param Garden $garden
     *
     * @return array
     */
    private function populateData($data, $garden = null) {
        if (!isset($data['can_perish'])) {
            $data['can_perish'] = 0;
        }
        if (!isset($data['is_active'])) {
            $data['is_active'] = 1;
        }

        if (isset($data['remove_image'])) {
            if ($garden && $garden->has_image && $data['remove_image']) {
                $data['has_image'] = 0;
                $this->deleteImage($garden->imagePath, $garden->imageFileName);
            }
            unset($data['remove_image']);
        }

        if (isset($data['remove_background_image'])) {
            if ($garden && $garden->has_background_image && $data['remove_background_image']) {
                $data['has_background_image'] = 0;
                $this->deleteImage($garden->backgroundImagePath, $garden->backgroundImageFileName);
            }
            unset($data['remove_background_image']);
        }

        if (isset($data['remove_plot_image'])) {
            if ($garden && $garden->has_plot_image && $data['remove_plot_image']) {
                $data['has_plot_image'] = 0;
                $this->deleteImage($garden->plotImagePath, $garden->plotImageFileName);
            }
            unset($data['remove_plot_image']);
        }

        return $data;
    }

    /**
     * Processes user input for creating/updating a garden object.
     *
     * @param array  $data
     * @param Garden $garden
     *
     * @return array
     */
    private function populateObjectData($data, $object = null, $garden = null) {
        if ($data['type'] == 'seed' && isset($object)) {
            $data['stages'] = $this->handleStageImages($data, $object);
        }

        $data['rewards'] = getDataReadyAssets(processRewardableData($data, true, true));
        $data['potential_rewards'] = getDataReadyAssets(processRewardableData($data, true, true, 'potential_'));

        return Arr::except($data, [
            '_token', 'type', 'object_model', 'object_id', 'rewardable_type', 'rewardable_id', 'quantity',
            'potential_rewardable_type', 'potential_rewardable_id', 'potential_quantity',
        ]);
    }

    /**
     * Handles stage images for anything that has variable stages,
     * and no column signifying the number of stages. (such as seed objects)
     *
     * @param array $data
     * @param GardenObject $object
     *
     * @return array
     */
    private function handleStageImages($data, $object) {
        if (isset($data['stage_number'])) {
            $data['stage_number'] = array_filter($data['stage_number']);
        }
        if (isset($data['stage_image'])) {
            $data['stage_image'] = array_filter($data['stage_image']);
        }
        ///
        $stages = [];
        if (isset($data['stage_number'])) {
            // if old stages exist
            if (isset($object->data['stages'])) {
                foreach ($object->data['stages'] as $stage) {
                    // if the stage number is not contained in $data['stage_number'], then delete the old image
                    if (!in_array($stage, $data['stage_number'])) {
                        if (file_exists(public_path('images/data/gardens/objects/'.$object->id.'-'.$stage.'-image.png'))) {
                            $this->deleteImage(public_path('images/data/gardens/objects'), $object->id.'-'.$stage.'-image.png');
                        }
                    } else {
                        $stages[] = $stage;
                    } // make sure there are no duplicates
                }
            }

            $stages = array_unique(array_merge($stages, $data['stage_number']));
            // now go thru new stages
            foreach ($stages as $key => $stage) {
                if (isset($data['stage_image'][$key])) {
                    $this->handleImage($data['stage_image'][$key], public_path('images/data/gardens/objects'), $object->id.'-'.$stage.'-image.png');
                }
            }
        } else {
            if (isset($object->data['stages']) && !empty($object->data['stages']) && $object->data['stages'] != '[]') {
                foreach ($object->data['stages'] as $stage) {
                    $this->deleteImage(public_path('iimages/data/gardens/objects'), $object->id.'-'.$stage.'-image.png');
                }
                // delete all of them that begin with the tag id (just in case)
                // match all files that begin with the tag id
                $files = glob(public_path('images/data/gardens/objects/'.$object->id.'-*'));
                // iterate through the files and delete them
                foreach ($files as $file) {
                    if (is_file($file)) {
                        unlink($file);
                    }
                }
            }
        }

        unset($data['stage_number']);
        unset($data['stage_image']);

        return $stages;
    }
}
