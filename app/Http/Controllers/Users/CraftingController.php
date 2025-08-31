<?php

namespace App\Http\Controllers\Users;

use App\Http\Controllers\Controller;
use App\Models\Item\Item;
use App\Models\Item\ItemCategory;
use App\Models\Recipe\Recipe;
use App\Models\Recipe\RecipeCategory;
use App\Models\Recipe\RecipeSlot;
use App\Models\User\User;
use App\Models\User\UserItem;
use App\Models\User\UserRecipeSlot;
use App\Services\RecipeManager;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CraftingController extends Controller {
    /*
    |--------------------------------------------------------------------------
    | Crafting Controller
    |--------------------------------------------------------------------------
    |
    | Handles viewing the user's available and locked recipes, as well as their usage.
    |
    */

    /**
     * Shows the user's trades.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getIndex(Request $request) {
        $categories = RecipeCategory::visible(Auth::user() ?? null)->orderBy('sort', 'DESC')->get();

        return view('home.crafting.index', [
            'default'     => Recipe::active()
                ->where('needs_unlocking', '0')
                ->when($categories->isNotEmpty(), function ($query) use ($categories) {
                    return $query->orderByRaw('FIELD(recipe_category_id,'.implode(',', $categories->pluck('id')->toArray()).')');
                })
                ->get(),
            'slots'       => RecipeSlot::get(),
            'userSlots'   => UserRecipeSlot::where('user_id', Auth::user()->id)->get(),
        ]);
    }

    /**
     * Shows a recipe's crafting modal.
     *
     * @param int $id
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getCraftRecipe(RecipeManager $service, $id) {
        $recipe = Recipe::find($id);
        $selected = [];

        if (!$recipe || !Auth::user() || !$recipe->active()->first()) {
            abort(404);
        }
        // foreach ingredient, search for a qualifying item in the users inv, and select items up to the quantity, if insufficient continue onto the next entry
        // until there are no more eligible items, then proceed to the next item
        $selected = $service->pluckIngredients(Auth::user(), $recipe);
        $inventory = UserItem::with('item')->whereNull('deleted_at')->where('count', '>', '0')->where('user_id', Auth::user()->id)->get();

        $userSlots = Auth::user()->craftingslots->where('recipe_id', null)->where('started_at', null)->where('end_at', null);
        // map userSlots to the RecipeSlot name
        $slots = $userSlots->mapWithKeys(function ($slot) {
            return [$slot->id => $slot->slot->name];
        });

        return view('home.crafting._modal_craft', [
            'recipe'            => $recipe,
            'categories'        => ItemCategory::orderBy('sort', 'DESC')->get(),
            'item_filter'       => Item::orderBy('name')->get()->keyBy('id'),
            'inventory'         => $inventory,
            'page'              => 'crafting',
            'selected'          => $selected,
            'slots'             => $slots,
        ]);
    }

    /**
     * Crafts a recipe.
     *
     * @param int $id
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function postCraftRecipe(Request $request, RecipeManager $service, $id) {
        $recipe = Recipe::find($id);
        if (!$recipe || !$recipe->active()->first()) {
            abort(404);
        }

        if ($service->craftRecipe($request->only(['stack_id', 'stack_quantity', 'slot_id', 'choice_reward']), $recipe, Auth::user())) {
            flash('Recipe ' . ($recipe->time ? 'started crafting' : 'crafted') . ' successfully.')->success();
        } else {
            foreach ($service->errors()->getMessages()['error'] as $error) {
                flash($error)->error();
            }
        }

        return redirect()->back();
    }

    /**
     * Claims a recipe.
     *
     * @param int $id
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postClaimRecipe(RecipeManager $service, $id) {
        UserRecipeSlot::findOrFail($id);

        if ($service->claimRecipe(UserRecipeSlot::find($id), Auth::user())) {
            flash('Recipe claimed successfully!')->success();
        } else {
            foreach ($service->errors()->getMessages()['error'] as $error) {
                flash($error)->error();
            }
        }

        return redirect()->back();
    }

    /**
     * Unlocks a recipe slot.
     *
     * @param int $id
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postUnlockRecipeSlot(Request $request, RecipeManager $service, $id) {
        $slot = RecipeSlot::findOrFail($id);

        if ($service->unlockRecipeSlot($slot, Auth::user())) {
            flash('Recipe slot unlocked successfully!')->success();
        } else {
            foreach ($service->errors()->getMessages()['error'] as $error) {
                flash($error)->error();
            }
        }

        return redirect()->back();
    }
}
