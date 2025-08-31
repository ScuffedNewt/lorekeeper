<?php

namespace App\Services;

use App\Models\Currency\Currency;
use App\Models\Recipe\Recipe;
use App\Models\Recipe\RecipeSlot;
use App\Models\User\User;
use App\Models\User\UserItem;
use App\Models\User\UserRecipeSlot;
use Carbon\Carbon;
use DB;

class RecipeManager extends Service {
    /**********************************************************************************************

        RECIPE CRAFTING

    **********************************************************************************************/

    /**
     * Attempts to craft the specified recipe.
     *
     * @param array  $data
     * @param Recipe $recipe
     * @param User   $user
     *
     * @return bool
     */
    public function craftRecipe($data, $recipe, $user) {
        DB::beginTransaction();

        try {
            if ($recipe->is_choice && !isset($data['choice_reward'])) {
                throw new \Exception('Please select a reward to craft.');
            }

            // Check user has all limits
            if (hasLimits($recipe)) {
                $limitService = new LimitManager;
                if (!$limitService->checkLimits($recipe)) {
                    throw new \Exception($limitService->errors()->getMessages()['error'][0]);
                }
            }

            // Check for sufficient currencies
            $user_currencies = $user->getCurrencies(true);
            $currency_ingredients = $recipe->ingredients->where('ingredient_type', 'Currency');
            foreach ($currency_ingredients as $ingredient) {
                $currency = $user_currencies->where('id', $ingredient->data[0])->first();
                if ($currency->quantity < $ingredient->quantity) {
                    throw new \Exception('Insufficient currency.');
                }
            }

            // If there are non-Currency ingredients.
            if (isset($data['stack_id'])) {
                // Fetch the stacks from DB
                $stacks = UserItem::whereIn('id', $data['stack_id'])->get()->map(function ($stack) use ($data) {
                    $stack->count = (int) $data['stack_quantity'][$stack->id];

                    return $stack;
                });

                // Check for sufficient ingredients
                $pluckedData = $this->pluckIngredients($user, $recipe, $stacks);
                if (!$pluckedData) {
                    throw new \Exception('Insufficient ingredients selected.');
                }

                $plucked = $pluckedData;
                // Debit the ingredients
                $service = new InventoryManager;
                foreach ($plucked as $id => $quantity) {
                    $stack = UserItem::find($id);
                    if (!$service->debitStack($user, 'Crafting', ['data' => 'Used in '.$recipe->displayName.' recipe'], $stack, $quantity)) {
                        throw new \Exception('Items could not be removed.');
                    }
                }
            } else {
                $items = $recipe->ingredients->where('ingredient_type', 'Item');
                if (count($items) > 0) {
                    throw new \Exception('Insufficient ingredients selected.');
                }
            }

            // Debit the currency
            $service = new CurrencyManager;
            foreach ($currency_ingredients as $ingredient) {
                if (!$service->debitCurrency($user, null, 'Crafting', 'Used in '.$recipe->displayName.' recipe', Currency::find($ingredient->data[0]), $ingredient->quantity)) {
                    throw new \Exception('Currency could not be debited.');
                }
            }

            // check slot data is valid
            if ($recipe->time || $recipe->required_slot_id) {
                if (!isset($data['slot_id'])) {
                    throw new \Exception('Invalid slot selected - this recipe requires you to use a slot');
                }
                $userSlot = UserRecipeSlot::find($data['slot_id']);
                if (!$userSlot) {
                    throw new \Exception('Invalid slot selected - this recipe requires you to use a slot');
                }

                // check if its the required slot
                if ($recipe->required_slot_id && $userSlot->slot_id != $recipe->required_slot_id) {
                    throw new \Exception('This recipe requires you to use the '.$recipe->requiredSlot->name.' slot.');
                }

                // check if the slot has any limits
                $slot = RecipeSlot::find($userSlot->slot_id);
                $service = new LimitManager;
                if (!$service->checkLimits($slot, true)) {
                    throw new \Exception($service->errors()->getMessages()['error'][0]);
                }
            }

            // if the recipe has a cook time we need to use a different function
            if ($recipe->time) {
                // save things
                $userSlot->recipe_id = $recipe->id;
                $userSlot->started_at = Carbon::now();
                $userSlot->end_at = Carbon::now()->addMinutes($recipe->time);
                if ($recipe->is_choice) {
                    $userSlot->choice_reward_data = $data['choice_reward'];
                }
                $userSlot->save();
            } else {
                // Credit rewards
                $logType = 'Crafting Reward';
                $craftingData = [
                    'data' => 'Received rewards from '.$recipe->displayName.' recipe',
                ];

                if ($recipe->is_choice) {
                    $matchReward = [];
                    preg_match('/([a-z\_]+)-([0-9]+)/', $data['choice_reward'], $matchReward);
                    if ($matchReward == [] || !isset($matchReward[1]) || !isset($matchReward[2])) {
                        throw new \Exception('Unable to get reward information.');
                    }
                    if (!isset($recipe->output[$matchReward[1]]) || !isset($recipe->output[$matchReward[1]][$matchReward[2]])) {
                        throw new \Exception('Unable to find reward in recipe\'s output.');
                    }

                    $choiceReward[$matchReward[1]] = [$matchReward[2] => $recipe->output[$matchReward[1]][$matchReward[2]]];
                    if (!fillUserAssets(parseAssetData($choiceReward), null, $user, $logType, $craftingData)) {
                        throw new \Exception('Failed to distribute chosen reward to user.');
                    }
                } else {
                    if (!fillUserAssets(parseAssetData($recipe->output), null, $user, $logType, $craftingData)) {
                        throw new \Exception('Failed to distribute rewards to user.');
                    }
                }
            }

            return $this->commitReturn(true);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }

    /**
     * Plucks stacks from a given Collection of user items that meet the crafting requirements of a recipe
     * If there are insufficient ingredients, null is returned.
     *
     * @param Recipe     $recipe
     * @param mixed      $user
     * @param mixed|null $selectedStacks
     *
     * @return array|null
     */
    public function pluckIngredients($user, $recipe, $selectedStacks = null) {
        $user_items = UserItem::with('item')->whereNull('deleted_at')->where('count', '>', '0')->where('user_id', $user->id)->get();
        $plucked = [];
        // foreach ingredient, search for a qualifying item, and select items up to the quantity, if insufficient continue onto the next entry
        foreach ($recipe->ingredients->sortBy('ingredient_type') as $ingredient) {
            if ($selectedStacks) {
                switch ($ingredient->ingredient_type) {
                    case 'Item':
                        $stacks = $selectedStacks->where('item.id', $ingredient->data[0]);
                        break;
                    case 'MultiItem':
                        $stacks = $selectedStacks->whereIn('item.id', $ingredient->data);
                        break;
                    case 'Category':
                        $stacks = $selectedStacks->where('item.item_category_id', $ingredient->data[0]);
                        break;
                    case 'MultiCategory':
                        $stacks = $selectedStacks->whereIn('item.item_category_id', $ingredient->data);
                        break;
                    case 'Currency':
                        continue 2;
                }
            } else {
                switch ($ingredient->ingredient_type) {
                    case 'Item':
                        $stacks = $user_items->where('item.id', $ingredient->data[0]);
                        break;
                    case 'MultiItem':
                        $stacks = $user_items->whereIn('item.id', $ingredient->data);
                        break;
                    case 'Category':
                        $stacks = $user_items->where('item.item_category_id', $ingredient->data[0]);
                        break;
                    case 'MultiCategory':
                        $stacks = $user_items->whereIn('item.item_category_id', $ingredient->data);
                        break;
                    case 'Currency':
                        continue 2;
                }
            }

            $quantity_left = $ingredient->quantity;
            while ($quantity_left > 0 && count($stacks) > 0) {
                $stack = $stacks->pop();
                $plucked[$stack->id] = $stack->count >= $quantity_left ? $quantity_left : $stack->count;
                // Update the larger collection
                $user_items = $user_items->map(function ($s) use ($stack, $plucked) {
                    if ($s->id == $stack->id) {
                        $s->count -= $plucked[$stack->id];
                    }
                    if ($s->count) {
                        return $s;
                    } else {
                        return null;
                    }
                })->filter();
                $quantity_left -= $plucked[$stack->id];
            }
            // If there are no more eligible ingredients but the requirement is not fulfilled, the pluck fails
            if ($quantity_left > 0) {
                return null;
            }
        }

        return $plucked;
    }

    /**
     * Claims a recipe that has been crafted and rewards the user.
     *
     * @param mixed $userSlot
     * @param mixed $user
     */
    public function claimRecipe($userSlot, $user) {
        DB::beginTransaction();

        try {
            if (!$userSlot) {
                throw new \Exception('Invalid slot.');
            }
            $recipe = Recipe::find($userSlot->recipe_id);
            if (!$recipe) {
                throw new \Exception('Invalid recipe stored.');
            }

            // Credit rewards
            $logType = 'Crafting Reward';
            $craftingData = [
                'data' => 'Received rewards from '.$recipe->displayName.' recipe',
            ];

            if ($recipe->is_choice) {
                $matchReward = [];
                preg_match('/([a-z\_]+)-([0-9]+)/', $userSlot->choice_reward_data, $matchReward);
                if ($matchReward == [] || !isset($matchReward[1]) || !isset($matchReward[2])) {
                    throw new \Exception('Unable to get reward information.');
                }
                if (!isset($recipe->output[$matchReward[1]]) || !isset($recipe->output[$matchReward[1]][$matchReward[2]])) {
                    throw new \Exception('Unable to find reward in recipe\'s output.');
                }

                $choiceReward[$matchReward[1]] = [$matchReward[2] => $recipe->output[$matchReward[1]][$matchReward[2]]];
                if (!fillUserAssets(parseAssetData($choiceReward), null, $user, $logType, $craftingData)) {
                    throw new \Exception('Failed to distribute chosen reward to user.');
                }
            } else {
                if (!fillUserAssets(parseAssetData($recipe->output), null, $user, $logType, $craftingData)) {
                    throw new \Exception('Failed to distribute rewards to user.');
                }
            }

            $userSlot->recipe_id = null;
            $userSlot->started_at = null;
            $userSlot->end_at = null;
            $userSlot->choice_reward_data = null;
            $userSlot->save();

            return $this->commitReturn(true);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }

    /**********************************************************************************************

        RECIPE UNLOCKING

    **********************************************************************************************/

    /**
     * Unlocks a recipe slot.
     *
     * @param RecipeSlot $slot
     * @param User $user
     *
     * @return bool
     */
    public function unlockRecipeSlot($slot, $user) {
        DB::beginTransaction();

        try {
            if (!$slot || !$user) {
                throw new \Exception('Invalid slot or user.');
            }

            // check if user has already unlocked the slot
            if (UserRecipeSlot::where('slot_id', $slot->id)->where('user_id', $user->id)->exists()) {
                throw new \Exception('Recipe slot is already unlocked.');
            }

            if (hasLimits($slot) && getLimits($slot)->first()->is_unlocked) {
                $service = new LimitManager;
                if (!$service->checkLimits($slot, true)) {
                    throw new \Exception($service->errors()->getMessages()['error'][0]);
                }
            } // if it's unlocked debit the limits now, otherwise we debit the limits everytime they craft

            $slot = UserRecipeSlot::create([
                'slot_id' => $slot->id,
                'user_id' => $user->id,
            ]);

            return $this->commitReturn(true);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }
}
