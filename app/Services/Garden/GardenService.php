<?php

namespace App\Services\Garden;

use App\Models\Garden\Garden;
use App\Models\Garden\GardenObject;
use App\Models\Garden\GardenPlot;
use App\Models\Garden\UserGardenPlot;
use App\Services\Service;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class GardenService extends Service {
    /*
    |--------------------------------------------------------------------------
    | Garden Service
    |--------------------------------------------------------------------------
    |
    | Handles the creation and editing of garden plots, garden objects, and gardens.
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
                $image = $data['image'];
                unset($data['image']);
            } else {
                $data['has_image'] = 0;
                unset($data['image']);
            }

            $backgroundImage = null;
            if (isset($data['background_image']) && $data['background_image']) {
                $data['has_background_image'] = 1;
                $backgroundImage = $data['background_image'];
                unset($data['background_image']);
            } else {
                $data['has_background_image'] = 0;
                unset($data['background_image']);
            }

            $plotImage = null;
            if (isset($data['plot_image']) && $data['plot_image']) {
                $data['has_plot_image'] = 1;
                $plotImage = $data['plot_image'];
                unset($data['plot_image']);
            } else {
                $data['has_plot_image'] = 0;
                unset($data['plot_image']);
            }

            if ($image || $backgroundImage || $plotImage) {
                $data['hash'] = randomString(10);
            }

            $garden = Garden::create($data);

            if (!$this->logAdminAction($user, 'Created Garden', 'Created '.$garden->displayName)) {
                throw new \Exception('Failed to log admin action.');
            }

            if ($image) {
                $this->handleImage($image, $garden->imagePath, $garden->imageFileName);
            }
            if ($backgroundImage) {
                $this->handleImage($backgroundImage, $garden->imagePath, $garden->backgroundImageFileName);
            }
            if ($plotImage) {
                $this->handleImage($plotImage, $garden->imagePath, $garden->plotImageFileName);
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
                $image = $data['image'];
                unset($data['image']);
            } else {
                unset($data['image']);
            }

            $backgroundImage = null;
            if (isset($data['background_image']) && $data['background_image']) {
                $data['has_background_image'] = 1;
                $backgroundImage = $data['background_image'];
                unset($data['background_image']);
            } else {
                unset($data['background_image']);
            }

            $plotImage = null;
            if (isset($data['plot_image']) && $data['plot_image']) {
                $data['has_plot_image'] = 1;
                $plotImage = $data['plot_image'];
                unset($data['plot_image']);
            } else {
                unset($data['plot_image']);
            }

            if ($image || $backgroundImage || $plotImage) {
                $data['hash'] = randomString(10);
            }

            $garden->update($data);

            if (!$this->logAdminAction($user, 'Updated Garden', 'Updated '.$garden->displayName)) {
                throw new \Exception('Failed to log admin action.');
            }

            if ($image) {
                $this->handleImage($image, $garden->imagePath, $garden->imageFileName);
            }
            if ($backgroundImage) {
                $this->handleImage($backgroundImage, $garden->imagePath, $garden->backgroundImageFileName);
            }
            if ($plotImage) {
                $this->handleImage($plotImage, $garden->imagePath, $garden->plotImageFileName);
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
            if (UserGardenPlot::whereIn('plot_id', $garden->plots()->pluck('id'))->exists()) {
                throw new \Exception('This garden has user plots and cannot be deleted until they are cleared.');
            }

            if (!$this->logAdminAction($user, 'Deleted Garden', 'Deleted '.$garden->name)) {
                throw new \Exception('Failed to log admin action.');
            }

            $garden->plots()->delete();
            if ($garden->has_image && file_exists(public_path($garden->imageDirectory.'/'.$garden->imageFileName))) {
                $this->deleteImage($garden->imagePath, $garden->imageFileName);
            }
            if ($garden->has_background_image && file_exists(public_path($garden->backgroundImagePath.'/'.$garden->backgroundImageFileName))) {
                $this->deleteImage($garden->imagePath, $garden->backgroundImageFileName);
            }
            if ($garden->has_plot_image && file_exists(public_path($garden->plotImagePath.'/'.$garden->plotImageFileName))) {
                $this->deleteImage($garden->imagePath, $garden->plotImageFileName);
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
            if (!isset($data['can_perish'])) {
                $data['can_perish'] = 0;
            }
            if (!isset($data['ignore_seasons'])) {
                $data['ignore_seasons'] = 0;
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
                'data'      => Arr::only($data, ['allowed_object_types', 'can_perish', 'ignore_seasons', 'name']),
                'has_image' => !isset($data['has_image']) ? $plot->has_image : $data['has_image'],
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

    /**
     * Deletes a garden plot.
     *
     * @param mixed $plot
     */
    public function deleteGardenPlot($plot) {
        DB::beginTransaction();

        try {
            if (!$plot) {
                throw new \Exception('Plot not found.');
            }
            if (DB::table('user_garden_plots')->where('plot_id', $plot->id)->exists()) {
                throw new \Exception('At least one user currently owns this plot. Please remove the plot(s) before deleting it.');
            }
            if ($plot->has_image && file_exists(public_path($plot->imagePath.'/'.$plot->imageFileName))) {
                $this->deleteImage($plot->imagePath, $plot->imageFileName);
            }
            // delete all user plots associated with this plot
            DB::table('user_garden_plots')->where('plot_id', $plot->id)->delete();
            $plot->delete();

            return $this->commitReturn(true);
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
            if (GardenObject::where('object_model', $data['object_model'])->where('object_id', $data['object_id'])->exists()) {
                throw new \Exception('There is already an object with that model and ID. Please edit the existing object.');
            }
            if (!isset($data['type'])) {
                throw new \Exception('Type is required.');
            }
            if (!isset($data['object_model'])) {
                throw new \Exception('Object model is required.');
            }
            if (!isset($data['object_id'])) {
                throw new \Exception('Object ID is required.');
            }

            // Stage images need the persisted object's ID for their filenames.
            // Build the ordinary data first, then process stages after creation.
            $objectData = $this->populateObjectData($data);

            $object = GardenObject::create([
                'type'         => $data['type'],
                'object_model' => $data['object_model'],
                'object_id'    => $data['object_id'],
                'data'         => $objectData,
            ]);

            if ($data['type'] == 'seed') {
                $objectData['stages'] = $this->handleStageImages($data, $object);
                $object->update(['data' => $objectData]);
            }

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
            if (GardenObject::where('object_model', $data['object_model'])->where('object_id', $data['object_id'])->where('id', '!=', $object->id)->exists()) {
                throw new \Exception('There is already an object with that model and ID. Please edit the existing object.');
            }
            if (!isset($data['type'])) {
                throw new \Exception('Type is required.');
            }
            if (!isset($data['object_model'])) {
                throw new \Exception('Object model is required.');
            }
            if (!isset($data['object_id'])) {
                throw new \Exception('Object ID is required.');
            }

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

    /**
     * Deletes an unused garden object.
     *
     * @param GardenObject          $object
     * @param \App\Models\User\User $user
     *
     * @return bool
     */
    public function deleteGardenObject($object, $user) {
        DB::beginTransaction();
        try {
            if (!$object) throw new \Exception('Garden object not found.');
            if (UserGardenPlot::where('data->object_id', $object->id)->exists()) {
                throw new \Exception('This object is currently in use in at least one garden plot.');
            }
            foreach (glob(public_path('images/data/gardens/objects/'.$object->id.'-*')) ?: [] as $file) {
                if (is_file($file)) unlink($file);
            }
            $object->delete();
            $this->logAdminAction($user, 'Deleted Garden Object', 'Deleted garden object #'.$object->id);
            return $this->commitReturn(true);
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
     * @param array      $data
     * @param Garden     $garden
     * @param mixed|null $object
     *
     * @return array
     */
    private function populateObjectData($data, $object = null, $garden = null) {
        if ($data['type'] == 'seed' && (!isset($data['perish_time']) || !is_numeric($data['perish_time']) || $data['perish_time'] < 1)) {
            throw new \Exception('The perish grace period must be at least one hour.');
        }
        if ($data['type'] == 'seed' && isset($object)) {
            $data['stages'] = $this->handleStageImages($data, $object);
        }
        if ($data['type'] == 'animal') {
            $data['food'] = getDataReadyAssets(processRewardableData($data, true, 'food_'));
            $data['stay_rewards'] = getDataReadyAssets(processRewardableData($data, true, 'stay_'));
            $data['remove_rewards'] = getDataReadyAssets(processRewardableData($data, true, 'remove_'));
        }

        if (in_array($data['type'], ['seed', 'animal'])) {
            foreach ($data['tool_rewardable_type'] ?? [] as $key => $type) {
                if ($type !== 'Item') {
                    throw new \Exception('Garden tool requirements must use item assets.');
                }
                $toolId = $data['tool_rewardable_id'][$key] ?? null;
                if (!GardenObject::where('type', 'tool')->where('object_model', 'App\\Models\\Item\\Item')->where('object_id', $toolId)->exists()) {
                    throw new \Exception('Each required tool item must also be configured as a garden tool object.');
                }
                $quantity = $data['tool_quantity'][$key] ?? null;
                if (filter_var($quantity, FILTER_VALIDATE_INT) === false || (int) $quantity < 1) {
                    throw new \Exception('Required tool quantities must be whole numbers of one or greater.');
                }
                $stage = $data['tool_stage'][$key] ?? null;
                if (filter_var($stage, FILTER_VALIDATE_INT) === false || (int) $stage < 0 || (int) $stage >= (int) ($data['interactions'] ?? 1)) {
                    throw new \Exception('Required tool stages must be whole numbers from 0 to one less than the number of interactions.');
                }
            }
            $data['tools'] = getDataReadyAssets(processRewardableData($data, true, 'tool_', ['stage']));
        }

        if ($data['type'] != 'animal') {
            $data['rewards'] = getDataReadyAssets(processRewardableData($data, true));
        }
        foreach ($data['potential_chance'] ?? [] as $chance) {
            if (!is_numeric($chance) || $chance < 0 || $chance > 100) {
                throw new \Exception('Potential reward chances must be between 0 and 100.');
            }
        }
        $data['potential_rewards'] = getDataReadyAssets(processRewardableData($data, true, 'potential_', ['chance']));

        return Arr::except($data, [
            '_token', 'type', 'object_model', 'object_id', 'stage_number', 'stage_image',
            'rewardable_type', 'rewardable_id', 'quantity',
            'potential_rewardable_type', 'potential_rewardable_id', 'potential_quantity', 'potential_chance',
            // animal exclusive
            'food_rewardable_type', 'food_rewardable_id', 'food_quantity',
            'tool_rewardable_type', 'tool_rewardable_id', 'tool_quantity', 'tool_stage',
            'stay_rewardable_type', 'stay_rewardable_id', 'stay_quantity',
            'remove_rewardable_type', 'remove_rewardable_id', 'remove_quantity',
        ]);
    }

    /**
     * Handles stage images for anything that has variable stages,
     * and no column signifying the number of stages. (such as seed objects).
     *
     * @param array        $data
     * @param GardenObject $object
     *
     * @return array
     */
    private function handleStageImages($data, $object) {
        $stageNumbers = array_filter($data['stage_number'] ?? [], function ($value) {
            return $value !== null && $value !== '';
        });
        $stageImages = $data['stage_image'] ?? [];
        $existingStages = array_map('intval', $object->data['stages'] ?? []);
        $stages = [];

        if ($stageNumbers) {
            foreach ($stageNumbers as $key => $stage) {
                if (filter_var($stage, FILTER_VALIDATE_INT) === false || (int) $stage < 0) {
                    throw new \Exception('Stage numbers must be whole numbers of zero or greater.');
                }

                $stage = (int) $stage;
                if (in_array($stage, $stages, true)) {
                    throw new \Exception('Each stage number must be unique.');
                }

                $hasUpload = isset($stageImages[$key]) && $stageImages[$key];
                if (!$hasUpload && !in_array($stage, $existingStages, true)) {
                    throw new \Exception('Stage '.$stage.' needs an image upload.');
                }

                $stages[] = $stage;
            }

            // Validate every row before writing any files, then use the original
            // row keys to keep each upload paired with its submitted stage.
            foreach ($stageNumbers as $key => $stage) {
                if (isset($stageImages[$key]) && $stageImages[$key]) {
                    $this->handleImage($stageImages[$key], public_path('images/data/gardens/objects'), $object->id.'-'.(int) $stage.'-image.png');
                }
            }

            foreach (array_diff($existingStages, $stages) as $removedStage) {
                $this->deleteImage(public_path('images/data/gardens/objects'), $object->id.'-'.$removedStage.'-image.png');
            }

            sort($stages);
        } else {
            if ($existingStages) {
                foreach ($existingStages as $stage) {
                    $this->deleteImage(public_path('images/data/gardens/objects'), $object->id.'-'.$stage.'-image.png');
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
