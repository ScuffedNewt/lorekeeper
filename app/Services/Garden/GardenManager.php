<?php

namespace App\Services\Garden;

use App\Facades\Notifications;
use App\Models\Garden\Garden;
use App\Models\Garden\GardenObject;
use App\Models\Garden\GardenPlot;
use App\Models\Garden\UserGardenPlot;
use App\Models\User\UserActivity;
use App\Models\User\UserItem;
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
            if ((!$garden->is_active && !$user->hasPower('manage_gardens')) || !hasUnlockedLimits($user, $garden)) {
                throw new \Exception('You have not unlocked this garden.');
            }

            $userPlot = $this->getUserPlot($user, $data);
            if ($userPlot->plot->garden_id != $garden->id) {
                throw new \Exception('The selected plot does not belong to this garden.');
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

            $gardenTypes = $garden->allowed_object_types ?: array_keys(config('lorekeeper.garden.garden_object_types'));
            $plotTypes = $userPlot->plot->data['allowed_object_types'] ?? [];
            $plotTypes = $plotTypes ?: $gardenTypes;
            if (!in_array($object->type, $gardenTypes) || !in_array($object->type, $plotTypes)) {
                throw new \Exception('This object type is not allowed in the selected plot.');
            }

            /*
             * WHEN INTRODUCING A NEW TYPE.
             *
             * Naming Convention:
             * place -> Objects that actually go into the garden
             * use -> Objects applied to a plot before placement, such as fertiliser
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
                default:
                    throw new \Exception('Invalid object type.');
            }

            $message = 'Successfully '.config('lorekeeper.garden.garden_object_types.'.$object->type.'.mode_text.place_past', 'placed').' '.($object->object?->name ?? 'object').' in the '.$garden->name.' garden.';

            return $this->commitReturn($message);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }

    /**
     * Tends to a plot.
     *
     * @param User  $user
     * @param mixed $data
     *
     * @return bool
     */
    public function tend($user, $data) {
        DB::beginTransaction();

        try {
            $userPlot = $this->getUserPlot($user, $data);
            if (!$userPlot->user_id || $userPlot->user_id != $user->id) {
                throw new \Exception('You do not own this plot.');
            }
            if (!$userPlot->started_at) {
                throw new \Exception('This plot has not been started yet.');
            }
            if ($userPlot->isPerished) {
                throw new \Exception('This seed has perished and must be cleared.');
            }
            if (!$userPlot->actionReady) {
                throw new \Exception('This plot is not ready for action yet.');
            }

            $object = $userPlot->object;
            if (!$object) {
                throw new \Exception('Garden object not found.');
            }

            if ($object->type == 'animal' && !empty($object->data['food'])) {
                $this->debitAssets($user, parseAssetData($object->data['food']), 'Fed animal in garden', ['data' => 'Fed '.$object->object->displayName.' in the '.$userPlot->garden->displayName.' garden']);
            }

            $plotData = $userPlot->data;
            $plotData['interacted_at'] = Carbon::now();
            $plotData['interacted_count'] = ($plotData['interacted_count'] ?? 0) + 1;
            $userPlot->update([
                'data' => $plotData,
            ]);

            // todo
            $message = null;
            if ($userPlot->actionReady && $userPlot->interacted_count >= $object->data['interactions']) {
                $message = 'Ready to '.config('lorekeeper.garden.garden_object_types.'.$object->type.'.mode_text.tend').' in:<br />'.pretty_date($userPlot->timeUntilReady);
            } else {
                $message = 'Can be '.config('lorekeeper.garden.garden_object_types.'.$object->type.'.mode_text.tend_past').' in '.pretty_date($userPlot->timeUntilReady);
            }

            return $this->commitReturn($message);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }

    /**
     * Collects the object from a plot.
     *
     * @param User  $user
     * @param mixed $data
     *
     * @return bool
     */
    public function collect($user, $data) {
        DB::beginTransaction();

        try {
            $userPlot = $this->getUserPlot($user, $data);
            if (!$userPlot->user_id || $userPlot->user_id != $user->id) {
                throw new \Exception('You do not own this plot.');
            }
            if (!$userPlot->started_at) {
                throw new \Exception('This plot has not been started yet.');
            }
            if ($userPlot->isPerished) {
                throw new \Exception('This seed has perished and must be cleared.');
            }
            if (!$userPlot->canCollect || $userPlot->actionReady) {
                throw new \Exception('This plot is not ready to be collected yet.');
            }

            $object = $userPlot->object;
            if (!$object) {
                throw new \Exception('Garden object not found.');
            }

            $collectionOutcome = 'remove';
            if ($object->type == 'animal') {
                $configuredOptions = $object->data['collection_options'];
                $requestedOutcome = $data['collection_outcome'] ?? null;
                $collectionOutcome = $configuredOptions === 'both' ? $requestedOutcome : $configuredOptions;
                if (!in_array($collectionOutcome, ['stay', 'remove'])) {
                    throw new \Exception('Choose whether the animal should stay or be removed.');
                }
            }

            $rewardData = $object->type == 'animal' ? $object->rawRewardsFor($collectionOutcome) : ($object->data['rewards'] ?? []);
            $definiteRewards = parseAssetData($rewardData);
            $definiteSummary = createRewardsString($definiteRewards) ?: 'None';

            $potentialRewards = createAssetsArray();
            foreach (parseAssetData($object->data['potential_rewards'] ?? []) as $assets) {
                foreach ($assets as $asset) {
                    $chance = (float) ($asset['data']['chance'] ?? 0);
                    if (random_int(1, 10000) <= (int) round($chance * 100)) {
                        addAsset($definiteRewards, $asset['asset'], $asset['quantity']);
                        addAsset($potentialRewards, $asset['asset'], $asset['quantity']);
                    }
                }
            }

            // Logging data
            $logType = 'Collected from Gardens';
            $logData = [
                'data' => 'Collected '.($object->object?->displayName ?? 'garden object').' from the '.$userPlot->garden->displayName,
            ];

            // Distribute user rewards
            if (!$rewards = fillUserAssets($definiteRewards, null, $user, $logType, $logData)) {
                throw new \Exception('Failed to distribute collected items to user.');
            }

            $potentialSummary = createRewardsString($potentialRewards);
            $collectionMessage = '<strong>Definite rewards:</strong> '.$definiteSummary.($potentialSummary ? '<br><strong>Successful potential rewards:</strong> '.$potentialSummary : '<br><strong>Potential rewards:</strong> None succeeded.');

            if ($object->type == 'animal' && $collectionOutcome === 'stay') {
                $plotData = $userPlot->data;
                unset($plotData['interacted_at'], $plotData['ready_notified_at'], $plotData['perished_notified_at']);
                $plotData['interacted_count'] = 0;
                $userPlot->update(['started_at' => Carbon::now(), 'data' => $plotData]);
            } else {
                if ($object->type == 'animal') {
                    $stack = UserItem::where('id', $userPlot->data['user_item_id'] ?? null)
                        ->where('user_id', $user->id)->lockForUpdate()->first();
                    if (!$stack || $stack->garden_count < 1 || $stack->count < 1) {
                        throw new \Exception('The animal reserved for this plot could not be found.');
                    }
                    $stack->garden_count--;
                    $stack->count--;
                    $stack->save();
                }

                $userPlot->update(['started_at' => null, 'data' => null]);
            }

            return $this->commitReturn($collectionMessage);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }

    /**
     * Releases an animal back to the user's available inventory without rewards.
     *
     * @param \App\Models\User\User $user
     * @param int                   $userPlotId
     *
     * @return bool
     */
    public function removeAnimal($user, $userPlotId) {
        DB::beginTransaction();
        try {
            $userPlot = UserGardenPlot::where('id', $userPlotId)->where('user_id', $user->id)->lockForUpdate()->first();
            if (!$userPlot || $userPlot->object?->type !== 'animal') {
                throw new \Exception('Animal plot not found.');
            }
            $stack = UserItem::where('id', $userPlot->data['user_item_id'])->where('user_id', $user->id)->lockForUpdate()->first();
            if (!$stack || $stack->garden_count < 1) {
                throw new \Exception('The animal reserved for this plot could not be found.');
            }
            $stack->garden_count--;
            $stack->save();
            $userPlot->update(['started_at' => null, 'data' => null]);

            return $this->commitReturn(true);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }

    /**
     * Tends a ready seed belonging to another user.
     *
     * @param \App\Models\User\User $guest
     * @param int                   $userPlotId
     *
     * @return bool|string
     */
    public function guestTend($guest, $userPlotId) {
        DB::beginTransaction();
        try {
            if (!$guest) {
                throw new \Exception('You must be logged in to water another user\'s garden.');
            }
            if (!config('lorekeeper.garden.allow_users_to_water_gardens')) {
                throw new \Exception('Guest watering is disabled.');
            }
            // Serialize allowance checks for this guest so simultaneous requests
            // cannot both observe the same remaining daily watering.
            DB::table('users')->where('id', $guest->id)->lockForUpdate()->first();
            $userPlot = UserGardenPlot::where('id', $userPlotId)->lockForUpdate()->first();
            if (!$userPlot || !$userPlot->started_at || $userPlot->isPerished || $userPlot->user_id == $guest->id || $userPlot->object?->type !== 'seed' || !$userPlot->actionReady) {
                throw new \Exception('This plant cannot be watered right now.');
            }
            $activity = UserActivity::firstOrCreate(['user_id' => $guest->id]);
            $limit = max(0, (int) config('lorekeeper.garden.user_guest_water_limit'));
            if ($limit && $activity->garden_guest_waters_today >= $limit) {
                throw new \Exception('You have reached today\'s guest watering limit.');
            }
            $plotData = $userPlot->data;
            $plotData['interacted_at'] = Carbon::now();
            $plotData['interacted_count'] = ($plotData['interacted_count'] ?? 0) + 1;
            unset($plotData['ready_notified_at'], $plotData['perished_notified_at']);
            $userPlot->update(['data' => $plotData]);
            $activity->garden_guest_waters_today++;
            $activity->save();

            $remaining = $limit ? max(0, $limit - $activity->garden_guest_waters_today) : null;
            $message = 'Plant watered.';
            $message .= $remaining === null
                ? ' Guest watering is unlimited.'
                : ' You have '.$remaining.' guest '.($remaining === 1 ? 'watering' : 'waterings').' remaining today.';

            return $this->commitReturn($message);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }

    /**
     * Performs every currently possible care action in a garden.
     *
     * @param \App\Models\User\User $user
     * @param int                   $gardenId
     *
     * @return int
     */
    public function careAll($user, $gardenId) {
        $count = 0;
        $plots = UserGardenPlot::where('user_id', $user->id)->whereHas('plot', fn ($query) => $query->where('garden_id', $gardenId))->get();
        foreach ($plots->filter->actionReady as $plot) {
            if ($this->tend($user, ['plot_id' => $plot->id])) {
                $count++;
            }
        }

        return $count;
    }

    /**
     * Collects every currently ready plot in a garden, keeping choice animals.
     *
     * @param \App\Models\User\User $user
     * @param int                   $gardenId
     *
     * @return int
     */
    public function collectAll($user, $gardenId) {
        $count = 0;
        $plots = UserGardenPlot::where('user_id', $user->id)->whereHas('plot', fn ($query) => $query->where('garden_id', $gardenId))->get();
        foreach ($plots->filter->canCollect as $plot) {
            if ($this->collect($user, ['plot_id' => $plot->id, 'collection_outcome' => 'stay'])) {
                $count++;
            }
        }

        return $count;
    }

    /**
     * Creates one notification per readiness or perish transition.
     *
     * @param UserGardenPlot $userPlot
     *
     * @return bool
     */
    public function syncNotifications($userPlot) {
        DB::beginTransaction();

        try {
            $userPlot = UserGardenPlot::where('id', $userPlot->id)->lockForUpdate()->first();
            if (!$userPlot?->started_at || !$userPlot->object) {
                return $this->commitReturn(false);
            }

            $data = $userPlot->data;
            if ($userPlot->isPerished && empty($data['perished_notified_at'])) {
                Notifications::create('GARDEN_PERISHED', $userPlot->user, [
                    'object' => $userPlot->object->object?->name ?? 'seed',
                ]);
                $data['perished_notified_at'] = Carbon::now();
            } elseif (($userPlot->actionReady || $userPlot->canCollect) && empty($data['ready_notified_at'])) {
                Notifications::create('GARDEN_READY', $userPlot->user, [
                    'object' => $userPlot->object->object?->name ?? 'object',
                ]);
                $data['ready_notified_at'] = Carbon::now();
            } else {
                return $this->commitReturn(false);
            }
            $userPlot->update(['data' => $data]);

            return $this->commitReturn(true);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }

    /**
     * Clears a perished seed from a user's plot.
     *
     * @param \App\Models\User\User $user
     * @param int                   $userPlotId
     *
     * @return bool
     */
    public function clearPerished($user, $userPlotId) {
        DB::beginTransaction();

        try {
            $userPlot = UserGardenPlot::where('id', $userPlotId)
                ->where('user_id', $user->id)->lockForUpdate()->first();
            if (!$userPlot) {
                throw new \Exception('User plot not found.');
            }
            if (!$userPlot->isPerished) {
                throw new \Exception('This plot does not contain a perished seed.');
            }

            $userPlot->update(['started_at' => null, 'data' => null]);

            return $this->commitReturn(true);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }

    /**
     * Gets a user plot based on the provided data. If the plot is unowned (but unlocked), it creates a new user plot for the specified plot.
     *
     * @param User  $user
     * @param mixed $data
     *
     * @return GardenPlot|null
     */
    public function getUserPlot($user, $data) {
        if (!isset($data['plot_id'])) {
            throw new \Exception('No plot id provided.');
        }

        $userPlot = null;
        if (strpos($data['plot_id'], 'unowned') !== false || strpos($data['plot_id'], 'plot') !== false) {
            $plotId = explode('-', $data['plot_id'])[1];
            $plot = GardenPlot::where('id', $plotId)->lockForUpdate()->first();
            if (!$plot) {
                throw new \Exception('Plot not found.');
            }
            // check if user owns the plot already
            $existingUserPlot = UserGardenPlot::where('user_id', $user->id)->where('plot_id', $plot->id)->first();
            if ($existingUserPlot) {
                return $existingUserPlot;
            }
            // now check if we CAN unlock the plot
            if (!hasUnlockedLimits($user, $plot)) {
                throw new \Exception('You must purchase this plot before you can place anything in it.');
            }
            // Create a user plot
            $userPlot = $this->createUserPlot($user, $plot);
        } else {
            $userPlot = UserGardenPlot::where('id', $data['plot_id'])->lockForUpdate()->first();
            if (!$userPlot || $userPlot->user_id != $user->id) {
                throw new \Exception('User plot not found.');
            }
        }

        return $userPlot;
    }

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
            if ($userPlot->started_at || $userPlot->object) {
                throw new \Exception('This plot is already occupied.');
            }
            $logType = 'Used in Garden';
            $data = [
                'data' => 'Used to plant a seed in the '.$garden->displayName.' garden',
            ];

            // check if the seed can be planted during this month
            $startMonth = $object->data['start_month'] ?? null;
            $endMonth = $object->data['end_month'] ?? $startMonth;
            if ($startMonth && !($userPlot->plot->data['ignore_seasons'] ?? false)) {
                if (!$object->isInSeason()) {
                    throw new \Exception('This seed cannot be planted during this month, it can only be planted between '.Carbon::create()->month($startMonth)->format('F').' and '.Carbon::create()->month($endMonth)->format('F').'.');
                }
            }

            $requiredQuality = $object->requiredQuality ?? 0;
            if ($requiredQuality > 0) {
                // Check the plot has the required quality
                $fertiliserId = $userPlot->modifiers['fertiliser'] ?? null;
                $plotQuality = $fertiliserId ? (GardenObject::find($fertiliserId)?->data['quality'] ?? 0) : 0;
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
                    $chosenStack = (new InventoryManager)->getDebitableStacks($user, $object->object_id, 1);
                    if (!$chosenStack) {
                        throw new \Exception('You do not have the required animal to place.');
                    }
                    break;
                    // Add more cases here for other models as needed, such as pets, characters, etc.
                default:
                    throw new \Exception('Invalid object model.');
            }
            foreach ($chosenStack as &$selection) {
                $selection['stack'] = UserItem::where('id', $selection['stack']->id)->lockForUpdate()->first();
                if (!$selection['stack'] || $selection['stack']->availableQuantity < $selection['quantity']) {
                    throw new \Exception('The selected seed is no longer available.');
                }
            }
            unset($selection);
            if (!takeUserAssets($debitAssets, $user, null, $logType, $data, $chosenStack)) {
                throw new \Exception('Failed to debit from user.');
            }

            $userPlot->update([
                'started_at' => Carbon::now(),
                'data'       => array_merge($userPlot->data ?? [], [
                    'object_id' => $object->id,
                ]),
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
            if ($userPlot->started_at || $userPlot->object) {
                throw new \Exception('This plot is already occupied.');
            }
            $logType = 'Used in Garden';
            $data = [
                'data' => 'Used to place an animal in the '.$garden->displayName.' ',
            ];

            $objectItem = $object->object_model::find($object->object_id);
            if (!$objectItem) {
                throw new \Exception('Invalid object.');
            }

            $chosenStacks = null;
            switch ($object->object_model) {
                case 'App\Models\Item\Item':
                    $chosenStacks = (new InventoryManager)->getDebitableStacks($user, $object->object_id, 1);
                    if (!$chosenStacks) {
                        throw new \Exception('You do not have the required animal to place.');
                    }
                    break;
                    // Add more cases here for other animal models as needed, such as pets, characters, etc.
                default:
                    throw new \Exception('Invalid object model for animal placement.');
            }

            $chosenStack = isset($chosenStacks[0]) ? $chosenStacks[0]['stack'] : null;
            $chosenStack = UserItem::where('id', $chosenStack?->id)->lockForUpdate()->first();
            // increase garden_count
            if ($chosenStack->availableQuantity < 1) {
                throw new \Exception('You do not have the required animal to place.');
            }
            $chosenStack->garden_count += 1;
            $chosenStack->save();
            $userPlot->update([
                'started_at' => Carbon::now(),
                'data'       => [
                    'object_id'    => $object->id,
                    'user_item_id' => $chosenStack->id,
                ],
            ]);

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
                'data' => 'Used to fertilise a plot in the '.$garden->displayName.' garden',
            ];

            if ($userPlot->started_at) {
                throw new \Exception('Fertiliser must be applied before placing an object.');
            }
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
            $this->debitAssets($user, $debitAssets, $logType, $data);

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

    /**********************************************************************************************

        PRIVATE FUNCTIONS

    **********************************************************************************************/

    /**
     * Debits the specified assets from the user, with error handling and logging.
     *
     * @param User   $user
     * @param array  $assets
     * @param string $log
     * @param array  $logType
     *
     * @return bool
     */
    private function debitAssets($user, $assets, $log, $logType) {
        $selected = [];
        $debitAssets = createAssetsArray();
        foreach ($assets as $type => $stack) {
            foreach ($stack as $id => $asset) {
                switch ($type) {
                    case 'items':
                        $chosenStacks = (new InventoryManager)->getDebitableStacks($user, $id, $asset['quantity']);
                        if (!$chosenStacks) {
                            throw new \Exception('You do not have the required items.');
                        }

                        $selected = array_merge($selected, $chosenStacks);
                        addAsset($debitAssets, $asset['asset'], $asset['quantity']);
                        break;
                    case 'currency':
                        addAsset($debitAssets, $asset['asset'], $asset['quantity']);
                        break;
                    default:
                        throw new \Exception('Invalid asset type for debiting.');
                        break;
                }
            }
        }

        foreach ($selected as &$selection) {
            $selection['stack'] = UserItem::where('id', $selection['stack']->id)->lockForUpdate()->first();
            if (!$selection['stack'] || $selection['stack']->availableQuantity < $selection['quantity']) {
                throw new \Exception('A required item is no longer available.');
            }
        }
        unset($selection);
        if (!takeUserAssets($debitAssets, $user, null, $log, $logType, $selected)) {
            throw new \Exception('Failed to debit from user.');
        }

        return true;
    }
}
