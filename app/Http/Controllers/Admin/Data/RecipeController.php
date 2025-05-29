<?php

namespace App\Http\Controllers\Admin\Data;

use App\Http\Controllers\Controller;
use App\Models\Currency\Currency;
use App\Models\Item\Item;
use App\Models\Item\ItemCategory;
use App\Models\Loot\LootTable;
use App\Models\Raffle\Raffle;
use App\Models\Recipe\Recipe;
use App\Models\Recipe\RecipeSlot;
use App\Services\RecipeService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RecipeController extends Controller {
    /*
    |--------------------------------------------------------------------------
    | Admin / Recipe Controller
    |--------------------------------------------------------------------------
    |
    | Handles creation/editing of recipes.
    |
    */

    /**********************************************************************************************

        RECIPES

    **********************************************************************************************/

    /**
     * Shows the recipe index.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getRecipeIndex(Request $request) {
        $query = Recipe::query();
        $data = $request->only(['name']);
        if (isset($data['name'])) {
            $query->where('name', 'LIKE', '%'.$data['name'].'%');
        }

        return view('admin.recipes.recipes', [
            'recipes' => $query->paginate(20)->appends($request->query()),
        ]);
    }

    /**
     * Shows the create recipe page.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getCreateRecipe() {
        return view('admin.recipes.create_edit_recipe', [
            'recipe'     => new Recipe,
            'items'      => Item::orderBy('name')->pluck('name', 'id'),
            'categories' => ItemCategory::orderBy('name')->pluck('name', 'id'),
            'currencies' => Currency::where('is_user_owned', 1)->orderBy('name')->pluck('name', 'id'),
            'tables'     => LootTable::orderBy('name')->pluck('name', 'id'),
            'raffles'    => Raffle::where('rolled_at', null)->where('is_active', 1)->orderBy('name')->pluck('name', 'id'),
            'recipes'    => Recipe::orderBy('name')->pluck('name', 'id'),
            'slots'      => RecipeSlot::orderBy('name')->pluck('name', 'id'),
        ]);
    }

    /**
     * Shows the edit recipe page.
     *
     * @param int $id
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getEditRecipe($id) {
        $recipe = Recipe::find($id);
        if (!$recipe) {
            abort(404);
        }

        return view('admin.recipes.create_edit_recipe', [
            'recipe'     => $recipe,
            'items'      => Item::orderBy('name')->pluck('name', 'id'),
            'categories' => ItemCategory::orderBy('name')->pluck('name', 'id'),
            'currencies' => Currency::where('is_user_owned', 1)->orderBy('name')->pluck('name', 'id'),
            'tables'     => LootTable::orderBy('name')->pluck('name', 'id'),
            'raffles'    => Raffle::where('rolled_at', null)->where('is_active', 1)->orderBy('name')->pluck('name', 'id'),
            'recipes'    => Recipe::orderBy('name')->pluck('name', 'id'),
            'slots'      => RecipeSlot::orderBy('name')->pluck('name', 'id'),
        ]);
    }

    /**
     * Creates or edits an recipe.
     *
     * @param App\Services\RecipeService $service
     * @param int|null                   $id
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postCreateEditRecipe(Request $request, RecipeService $service, $id = null) {
        $id ? $request->validate(Recipe::$updateRules) : $request->validate(Recipe::$createRules);
        $data = $request->only([
            'name', 'description', 'image', 'remove_image', 'needs_unlocking',
            'ingredient_type', 'ingredient_data', 'ingredient_quantity',
            'rewardable_type', 'rewardable_id', 'reward_quantity',
            'is_limited', 'limit_type', 'limit_id', 'limit_quantity',
            'close_at', 'open_at', 'time', 'required_slot_id', 'is_visible',
        ]);
        if ($id && $service->updateRecipe(Recipe::find($id), $data, Auth::user())) {
            flash('Recipe updated successfully.')->success();
        } elseif (!$id && $recipe = $service->createRecipe($data, Auth::user())) {
            flash('Recipe created successfully.')->success();

            return redirect()->to('admin/data/recipes/edit/'.$recipe->id);
        } else {
            foreach ($service->errors()->getMessages()['error'] as $error) {
                flash($error)->error();
            }
        }

        return redirect()->back();
    }

    /**
     * Gets the recipe deletion modal.
     *
     * @param int $id
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getDeleteRecipe($id) {
        $recipe = Recipe::find($id);

        return view('admin.recipes._delete_recipe', [
            'recipe' => $recipe,
        ]);
    }

    /**
     * Creates or edits an recipe.
     *
     * @param App\Services\RecipeService $service
     * @param int                        $id
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postDeleteRecipe(Request $request, RecipeService $service, $id) {
        if ($id && $service->deleteRecipe(Recipe::find($id))) {
            flash('Recipe deleted successfully.')->success();
        } else {
            foreach ($service->errors()->getMessages()['error'] as $error) {
                flash($error)->error();
            }
        }

        return redirect()->to('admin/data/recipes');
    }

    /**********************************************************************************************

        CRAFTING RECIPE SLOTS

    **********************************************************************************************/

    /**
     * Shows the crafting slots index.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getCraftingSlotIndex() {
        return view('admin.recipes.slots.index', [
            'slots' => RecipeSlot::all(),
        ]);
    }

    /**
     * Shows the create crafting slot page.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getCreateCraftingSlot() {
        return view('admin.recipes.slots.create_edit_slot', [
            'slot' => new RecipeSlot,
        ]);
    }

    /**
     * Shows the edit crafting slot page.
     *
     * @param int $id
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getEditCraftingSlot($id) {
        $slot = RecipeSlot::find($id);
        if (!$slot) {
            abort(404);
        }

        return view('admin.recipes.slots.create_edit_slot', [
            'slot'       => $slot,
        ]);
    }

    /**
     * Creates or edits an slot.
     *
     * @param App\Services\RecipeService $service
     * @param int|null                   $id
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postCreateEditCraftingSlot(Request $request, RecipeService $service, $id = null) {
        $data = $request->only(['name', 'description']);
        if ($id && $service->updateCraftingSlot(RecipeSlot::find($id), $data, Auth::user())) {
            flash('Slot updated successfully.')->success();
        } elseif (!$id && $slot = $service->createCraftingSlot($data, Auth::user())) {
            flash('Slot created successfully.')->success();

            return redirect()->to('admin/data/recipes/slots/edit/'.$slot->id);
        } else {
            foreach ($service->errors()->getMessages()['error'] as $error) {
                flash($error)->error();
            }
        }

        return redirect()->back();
    }

    /**
     * Gets the slot deletion modal.
     *
     * @param int $id
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getDeleteCraftingSlot($id) {
        $slot = RecipeSlot::find($id);

        return view('admin.recipes.slots._delete_slot', [
            'slot' => $slot,
        ]);
    }

    /**
     * Creates or edits an slot.
     *
     * @param App\Services\SlotService $service
     * @param int                      $id
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postDeleteCraftingSlot(Request $request, RecipeService $service, $id) {
        if ($id && $service->deleteCraftingSlot(RecipeSlot::find($id))) {
            flash('Slot deleted successfully.')->success();
        } else {
            foreach ($service->errors()->getMessages()['error'] as $error) {
                flash($error)->error();
            }
        }

        return redirect()->to('admin/data/recipes/slots');
    }
}
