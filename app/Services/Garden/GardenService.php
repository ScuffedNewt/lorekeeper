<?php

namespace App\Services\Garden;

use App\Models\Garden\Garden;
use App\Models\Garden\GardenPlot;
use App\Services\Service;
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
     * @return \App\Models\Garden\Garden|bool
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
     * @param \App\Models\Garden\Garden $garden
     * @param array                 $data
     * @param \App\Models\User\User $user
     *
     * @return \App\Models\Garden\Garden|bool
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
     * @param \App\Models\Garden\Garden $garden
     * @param mixed                 $user
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

    /**
     * Processes user input for creating/updating an garden.
     *
     * @param array                 $data
     * @param \App\Models\Garden\Garden $garden
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

    /**********************************************************************************************

        GARDEN PLOTS

    **********************************************************************************************/

    /**
     * Creates a new plot.
     *
     * @param \App\Models\Garden\Garden $garden
     *
     * @return \App\Models\Garden\GardenPlot|bool
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
}
