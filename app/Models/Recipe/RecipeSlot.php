<?php

namespace App\Models\Recipe;

use App\Models\Model;
use App\Models\User\UserRecipeSlot;

class RecipeSlot extends Model {
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'description',
    ];

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'recipe_slots';

    /**********************************************************************************************

        RELATIONS

    **********************************************************************************************/

    /**
     * Gets all of the user recipe slots that are using this recipe slot.
     */
    public function userRecipeSlots() {
        return $this->hasMany(UserRecipeSlot::class, 'slot_id');
    }

    /**********************************************************************************************

        ATTRIBUTES

    **********************************************************************************************/

    /**
     * Gets the name of the recipe slot.
     */
    public function getDisplayNameAttribute() {
        return $this->name;
    }

    /**
     * Returns the asset type of the slot.
     */
    public function getAssetTypeAttribute() {
        return 'recipe_slot';
    }
}
