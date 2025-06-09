<?php

namespace App\Models\Recipe;

use App\Models\Currency\Currency;
use App\Models\Item\Item;
use App\Models\Item\ItemCategory;
use App\Models\Model;
use App\Models\User\UserCurrency;

class RecipeIngredient extends Model {
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'recipe_id', 'ingredient_type', 'ingredient_data', 'quantity',
    ];

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'recipe_ingredients';

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'ingredient_data' => 'array',
    ];

    /**
     * Validation rules for creation.
     *
     * @var array
     */
    public static $createRules = [
        'recipe_id'       => 'required',
        'ingredient_type' => 'required',
        'ingredient_data' => 'required',
        'quantity'        => 'required|integer|min:1',
    ];

    /**
     * Validation rules for updating.
     *
     * @var array
     */
    public static $updateRules = [
        'recipe_id'       => 'required',
        'ingredient_type' => 'required',
        'ingredient_data' => 'required',
        'quantity'        => 'required|integer|min:1',
    ];

    /**********************************************************************************************

        RELATIONS

    **********************************************************************************************/

    /**
     * Get the associated recipe.
     */
    public function recipe() {
        return $this->belongsTo(Recipe::class, 'recipe_id');
    }

    /**********************************************************************************************

        ACCESSORS

    **********************************************************************************************/

    /**
     * Gets the json decoded data array.
     *
     * @return string
     */
    public function getDataAttribute() {
        return $this->ingredient_data;
    }

    /**
     * Gets the associated ingredient item(s) or category(ies).
     *
     * @return string
     */
    public function getIngredientAttribute() {
        switch ($this->ingredient_type) {
            case 'Item':
                return Item::where('id', $this->data[0])->first();
            case 'MultiItem':
                return Item::whereIn('id', $this->data)->get();
            case 'Category':
                return ItemCategory::where('id', $this->data[0])->first();
            case 'MultiCategory':
                return ItemCategory::whereIn('id', $this->data)->get();
            case 'Currency':
                return Currency::where('id', $this->data[0])->first();
        }

        return null;
    }

    /**********************************************************************************************

        OTHER FUNCTIONS

    **********************************************************************************************/

    /**
     * Returns if the user has enough of this ingredient.
     *
     * @param User $user
     *
     * @return bool
     */
    public function hasIngredient($user) {
        switch ($this->ingredient_type) {
            case 'Item':
                return $user->items()->where('item_id', $this->data[0])->sum('count') >= $this->quantity;
            case 'MultiItem':
                return $user->items()->whereIn('item_id', $this->data)->sum('count') >= $this->quantity;
            case 'Currency':
                return UserCurrency::where('user_id', $user->id)->where('currency_id', $this->data[0])->sum('quantity') >= $this->quantity;
        }

        return false;
    }
}
