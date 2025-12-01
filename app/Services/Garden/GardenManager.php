<?php

namespace App\Services\Garden;

use App\Models\Garden\Garden;
use App\Models\Garden\GardenObject;
use App\Models\Garden\GardenPlot;
use App\Models\Garden\UserGardenPlot;
use App\Models\Limit\Limit;
use App\Services\Service;
use Carbon\Carbon;
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
     * @param User  $user
     * @param array $data
     *
     * @return bool
     */
    public function place($user, $data) {
        DB::beginTransaction();

        try {
            \Log::info('GardenManager::place', $data);
            $garden = Garden::find($data['current_garden_id']);
            if (!$garden) {
                throw new \Exception('Garden not found.');
            }

            $userPlot = null;
            if (strpos($data['plot_id'], 'unowned') !== false) {
                $plotId = explode('-', $data['plot_id'])[1];
                $plot = GardenPlot::find($plotId);
                if (!$plot) {
                    throw new \Exception('Plot not found.');
                }
                if (Limit::hasLimits($plot)) {
                    throw new \Exception('You must purchase this plot before you can place anything in it.');
                }
                // Create a user plot
                $userPlot = $this->createUserPlot($user, $plot);
            } else {
                $userPlot = UserGardenPlot::find($data['plot_id']);
                if (!$userPlot || $userPlot->user_id != $user->id) {
                    throw new \Exception('User plot not found.');
                }
            }

            $model = getAssetModelString($data['stack_type']);
            // if the first character is a \ remove it
            if (substr($model, 0, 1) == '\\') {
                $model = substr($model, 1);
            }
            $object = GardenObject::where('type', $data['object_type'])
                ->where('object_model', $model)
                ->where('object_id', $data['stack_id'])->first();
            if (!$object) {
                throw new \Exception('Garden object not found.');
            }

            /**
             * WHEN INTRODUCING A NEW TYPE.
             *
             * Naming Convention:
             * place -> Objects that actually go into the garden
             * use -> Objects that are used on plots or to interact with objects, aka tools, fertilisers, food
             */
            $check = false;
            switch ($object->type) {
                case 'seed':
                    $check = $this->placeSeed($user, $userPlot, $garden, $object, $data);
                    break;
                case 'animal':
                    $check = $this->placeAnimal($user, $userPlot, $garden, $object, $data);
                    break;
                case 'fertiliser':
                    $check = $this->useFertiliser($user, $userPlot, $garden, $object, $data);
                    break;
                case 'tool':
                    $check = $this->useTool($user, $userPlot, $garden, $object, $data);
                    break;
                case 'food':
                    $check = $this->useFood($user, $userPlot, $garden, $object, $data);
                    break;
                default:
                    throw new \Exception('Invalid object type.');
            }

            if (!$check) {
                throw new \Exception('Failed to use object.');
            }

            return $this->commitReturn(true);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }

    /**
     * Interacts with a plot.
     *
     * @param User  $user
     * @param mixed $plot
     * @param mixed $data
     *
     * @return bool
     */
    public function interact($user, $plot, $data) {
        DB::beginTransaction();

        try {
            if (!$plot->user_id || $plot->user_id != $user->id) {
                throw new \Exception('You do not own this plot.');
            }
            if (!$plot->started_at) {
                throw new \Exception('This plot has not been started yet.');
            }
            if (!$plot->actionReady) {
                throw new \Exception('This plot is not ready for action yet.');
            }

            $object = $plot->object;
            if (!$object) {
                throw new \Exception('Garden object not found.');
            }

            // todo: debits etc if needed
            $plotData = $plot->data;
            $plotData['interacted_at'] = Carbon::now();
            $plotData['interacted_count'] = ($plotData['interacted_count'] ?? 0) + 1;
            $plot->update([
                'data' => $plotData,
            ]);

            return $this->commitReturn(true);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }

    /**
     * Collects the object from a plot.
     *
     * @param User           $user
     * @param UserGardenPlot $plot
     *
     * @return bool
     */
    public function collect($user, $plot) {
        DB::beginTransaction();

        try {
            if (!$plot->user_id || $plot->user_id != $user->id) {
                throw new \Exception('You do not own this plot.');
            }
            if (!$plot->started_at) {
                throw new \Exception('This plot has not been started yet.');
            }
            if (!$plot->canCollect) {
                throw new \Exception('This plot is not ready to be collected yet.');
            }

            $object = $plot->object;
            if (!$object) {
                throw new \Exception('Garden object not found.');
            }

            $definiteRewards = parseAssetData($object->data['rewards']);

            // TODO: use loot weighting on each reward
            $bonusChance = $object->data['bonus_chance'] ?? 0;
            if ($bonusChance > 0) {
                $bonus = rand(1, 100);
                if ($bonus <= $bonusChance) {
                    $bonusRewards = parseAssetData($object->data['potential_rewards']);
                    $definiteRewards = array_merge($definiteRewards, $bonusRewards);
                }
            }

            // Logging data
            $logType = 'Collected from Gardens';
            $logData = [
                'data' => 'Collected '.$object->displayName.' from the '.$plot->garden->displayName.'.',
            ];

            // Distribute user rewards
            if (!$rewards = fillUserAssets($definiteRewards, null, $user, $logType, $logData)) {
                throw new \Exception('Failed to distribute collected items to user.');
            }

            flash('You have collected: '.createRewardsString($rewards))->success();

            // Reset the plot
            $plot->update([
                'started_at' => null,
                'data'       => null,
            ]);

            return $this->commitReturn(true);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }

    /**********************************************************************************************

        PRIVATE FUNCTIONS

    **********************************************************************************************/

    /**********************************************************************************************

        PLACEMENT FUNCTIONS

    **********************************************************************************************/

    /**
     * Plants a seed in a plot.
     *
     * @param User           $user
     * @param UserGardenPlot $userPlot
     * @param Garden         $garden
     * @param GardenObject   $object
     *
     * @return bool
     */
    private function placeSeed($user, $userPlot, $garden, $object) {
        DB::beginTransaction();

        try {
            $logType = 'Used in Garden';
            $data = [
                'data' => 'Used to plant a seed in the '.$garden->displayName.' garden.',
            ];

            // check if the seed can be planted during this month
            $startMonth = $object->data['start_month'] ?? null;
            $endMonth = $object->data['end_month'] ?? null;
            if ($startMonth && $endMonth) {
                $currentMonth = Carbon::now()->month;
                if ($currentMonth < $startMonth || $currentMonth > $endMonth) {
                    throw new \Exception('This seed cannot be planted during this month, it can only be planted between '.Carbon::create()->month($startMonth)->format('F').' and '.Carbon::create()->month($endMonth)->format('F').'.');
                }
            }

            $requiredQuality = $object->data['quality'] ?? 0;
            if ($requiredQuality > 0) {
                // TODO: make sure fertiliser is used
            }

            // Debit the seed. Because we don't know what type the object is (variable) we can use the assets array
            $debitAssets = createAssetsArray();
            $objectItem = $object->object_model::find($object->object_id);
            if (!$objectItem) {
                throw new \Exception('Invalid object.');
            }
            addAsset($debitAssets, $objectItem->asset_id, -1);
            if (!$debitAssets = fillUserAssets($debitAssets, $user, null, $logType, $data)) {
                \Log::error('Failed to debit from user.');
                throw new \Exception('Failed to debit from user.');
            }

            $userPlot->update([
                'started_at' => Carbon::now(),
                'data'       => [
                    'object_id' => $object->id,
                ],
            ]);

            return $this->commitReturn(true);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }

    /**
     * Places an animal in a plot.
     *
     * @param User           $user
     * @param UserGardenPlot $userPlot
     * @param Garden         $garden
     * @param GardenObject   $object
     * @param mixed          $data
     *
     * @return bool
     */
    private function placeAnimal($user, $userPlot, $garden, $object, $data) {
        DB::beginTransaction();

        try {
            $logType = 'Used in Garden';
            $data = [
                'data' => 'Used to place an animal in the '.$garden->displayName.' garden.',
            ];

            $objectItem = $object->object_model::find($object->object_id);
            if (!$objectItem) {
                throw new \Exception('Invalid object.');
            }

            // TODO: FIND USER MODEL FOR $DATA['STACK_ID']

            \Log::info('GardenManager::placeAnimal', $objectItem->toArray());

            // increase garden_count
            // $user->update([
            //     'garden_count' => $user->garden_count + 1,
            // ]);

            // $userPlot->update([
            //     'started_at' => Carbon::now(),
            //     'data'       => [
            //         'object_id' => $object->id,
            //     ],
            // ]);

            return $this->commitReturn(true);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }

    /**********************************************************************************************

        PLOT FUNCTIONS

    **********************************************************************************************/

    /**
     * Creates a user plot for a specified plot.
     *
     * @param User       $user
     * @param GardenPlot $plot
     *
     * @return UserGardenPlot
     */
    private function createUserPlot($user, $plot) {
        DB::beginTransaction();

        try {
            $userPlot = UserGardenPlot::create([
                'user_id' => $user->id,
                'plot_id' => $plot->id,
            ]);

            return $this->commitReturn($userPlot);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }
}
