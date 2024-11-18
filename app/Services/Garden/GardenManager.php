<?php

namespace App\Services\Garden;

use App\Models\Garden\Garden;
use App\Models\Garden\GardenObject;
use App\Models\Garden\GardenPlot;
use App\Services\Service;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class GardenManager extends Service {
    /*
    |--------------------------------------------------------------------------
    | Garden Manager
    |--------------------------------------------------------------------------
    |
    | Handles the user interaction with the garden.
    |
    */

    /**
     * Places an object in the garden on a specified plot.
     * 
     * @param User $user
     * @param array $data
     */
    public function place($user, $data) {
        DB::beginTransaction();

        try {

            $garden = Garden::find($data['current_garden_id']);
            if (!$garden) {
                throw new \Exception('Garden not found.');
            }

            $userPlot = null;
            if (strpos($data['plot_id'], 'unowned') != false) {
                \Log::info('Unowned plot');
            } else {
                $userPlot = UserGardenPlot::find($data['plot_id']);

                if (!$userPlot || $userPlot->user_id != $user->id) {
                    throw new \Exception('Plot not found.');
                }
            }

            if (!$userPlot) {
                throw new \Exception('Plot not found.');
            }

            $object = GardenObject::find($data['type']);

            return $this->commitReturn(true);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }

}