<?php

namespace App\Services\Garden;

use App\Models\Garden\Garden;
use App\Models\Garden\GardenObject;
use App\Models\Garden\GardenPlot;
use App\Models\Garden\UserGardenPlot;
use App\Services\InventoryManager;
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
            $garden = Garden::find($data['current_garden_id']);
            if (!$garden) {
                throw new \Exception('Garden not found.');
            }

            $userPlot = null;
            if (strpos($data['plot_id'], 'unowned') !== false || strpos($data['plot_id'], 'plot') !== false) {
                $plotId = explode('-', $data['plot_id'])[1];
                $plot = GardenPlot::find($plotId);
                if (!$plot) {
                    throw new \Exception('Plot not found.');
                }
                if (!hasUnlockedLimits($user, $plot)) {
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

            $model = getAssetModelString($data['asset_type']);
            // if the first character is a \ remove it
            if (substr($model, 0, 1) == '\\') {
                $model = substr($model, 1);
            }
            $object = GardenObject::where('type', $data['object_type'])
                ->where('object_model', $model)
                ->where('object_id', $data['asset_id'])->first();
            if (!$object) {
                throw new \Exception('Garden object not found.');
            }

            /*
             * WHEN INTRODUCING A NEW TYPE.
             *
             * Naming Convention:
             * place -> Objects that actually go into the garden
             * use -> Objects that are used on plots or to interact with objects, aka tools, fertilisers, food
             */
            switch ($object->type) {
                case 'seed':
                    if (!$this->placeSeed($user, $userPlot, $garden, $object, $data)) {
                        throw new \Exception('Failed to plant seed.');
                    }
                    break;
                case 'animal':
                    if (!$this->placeAnimal($user, $userPlot, $garden, $object, $data)) {
                        throw new \Exception('Failed to place animal.');
                    }
                    break;
                case 'fertiliser':
                    if (!$this->useFertiliser($user, $userPlot, $garden, $object, $data)) {
                        throw new \Exception('Failed to use fertiliser.');
                    }
                    break;
                case 'tool':
                    if (!$this->useTool($user, $userPlot, $garden, $object, $data)) {
                        throw new \Exception('Failed to use tool.');
                    }
                    break;
                case 'food':
                    if (!$this->useFood($user, $userPlot, $garden, $object, $data)) {
                        throw new \Exception('Failed to use food.');
                    }
                    break;
                default:
                    throw new \Exception('Invalid object type.');
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
            // $bonusChance = $object->data['bonus_chance'] ?? 0;
            // if ($bonusChance > 0) {
            //     $bonus = rand(1, 100);
            //     if ($bonus <= $bonusChance) {
            //         $bonusRewards = parseAssetData($object->data['potential_rewards']);
            //         $definiteRewards = array_merge($definiteRewards, $bonusRewards);
            //     }
            // }

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
            $oldData = $plot->data;
            dd($oldData);
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
     * @param mixed          $data
     *
     * @return bool
     */
    private function placeSeed($user, $userPlot, $garden, $object, $data) {
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

            $requiredQuality = $object->requiredQuality ?? 0;
            if ($requiredQuality > 0) {
                // Check the plot has the required quality
                $plotQuality = $userPlot->modifiers['quality'] ?? 0;
                $plotQuality = 0;
                if ($plotQuality < $requiredQuality) {
                    throw new \Exception('This seed requires a fertiliser of quality '.$requiredQuality.' or higher to be planted.');
                }
            }
            // throw new \Exception('Seed quality requirement not implemented yet.');

            // Debit the seed. Because we don't know what type the object is (variable) we can use the assets array
            $debitAssets = createAssetsArray();
            $asset = $object->object_model::find($object->object_id);
            if (!$asset) {
                throw new \Exception('Invalid asset.');
            }
            addAsset($debitAssets, $asset, 1);
            $chosenStack = null;
            switch ($object->object_model) {
                case 'App\Models\Item\Item':
                    $chosenStack = (new InventoryManager)->getDebitableStack($user, $object->object_id, 1);
                    if (!$chosenStack) {
                        throw new \Exception('You do not have the required animal to place.');
                    }
                    break;
                    // Add more cases here for other models as needed, such as pets, characters, etc.
                default:
                    throw new \Exception('Invalid object model.');
            }
            if (!takeUserAssets($debitAssets, $user, null, $logType, $data, $chosenStack)) {
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

            \Log::info('GardenManager::placeAnimal', $objectItem->toArray());

            switch ($object->object_model) {
                case 'App\Models\Item\Item':
                    $chosenStack = (new InventoryManager)->getDebitableStack($user, $object->object_id, 1);
                    if (!$chosenStack) {
                        throw new \Exception('You do not have the required animal to place.');
                    }
                    break;
                    // Add more cases here for other animal models as needed, such as pets, characters, etc.
                default:
                    throw new \Exception('Invalid object model for animal placement.');
            }

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

        USE FUNCTIONS

    **********************************************************************************************/

    /**
     * Uses fertiliser on a plot.
     *
     * @param User           $user
     * @param UserGardenPlot $userPlot
     * @param Garden         $garden
     * @param GardenObject   $object
     * @param mixed          $data
     */
    private function useFertiliser($user, $userPlot, $garden, $object, $data) {
        DB::beginTransaction();

        try {
            $logType = 'Used in Garden';
            $data = [
                'data' => 'Used to fertilise a plot in the '.$garden->displayName.' garden.',
            ];

            $userModifierData = $userPlot->data['modifiers'] ?? [];
            if (!isset($userModifierData['fertiliser'])) {
                $userModifierData['fertiliser'] = $object->id;
            } else {
                throw new \Exception('This plot has already been fertilised.');
            }

            $userPlot->update([
                'data' => array_merge($userPlot->data ?? [], [
                    'modifiers' => $userModifierData,
                ]),
            ]);

            // Debit the fertiliser. Because we don't know what type the object is (variable) we can use the assets array
            $debitAssets = createAssetsArray();
            $objectItem = $object->object_model::find($object->object_id);
            if (!$objectItem) {
                throw new \Exception('Invalid object.');
            }
            addAsset($debitAssets, $objectItem, 1);
            if (!takeUserAssets($debitAssets, $user, null, $logType, $data, null, true)) {
                throw new \Exception('Failed to debit from user.');
            }

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
