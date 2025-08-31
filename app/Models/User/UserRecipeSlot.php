<?php

namespace App\Models\User;

use App\Models\Model;
use App\Models\Recipe\Recipe;
use App\Models\Recipe\RecipeSlot;

class UserRecipeSlot extends Model {

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'slot_id', 'user_id', 'recipe_id', 'started_at', 'choice_reward_data',
    ];

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'user_recipe_slots';

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'started_at' => 'datetime',
        'end_at'     => 'datetime',
    ];

    /**********************************************************************************************

        RELATIONS

    **********************************************************************************************/

    /**
     * Get the user who owns the stack.
     */
    public function user() {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the slot object associated with this slot record.
     */
    public function slot() {
        return $this->belongsTo(RecipeSlot::class, 'slot_id');
    }

    /**
     * Get the recipe currently being crafted in this slot.
     */
    public function recipe() {
        return $this->belongsTo(Recipe::class, 'recipe_id');
    }

    /**********************************************************************************************

        ATTRIBUTES

    **********************************************************************************************/

    /**
     * Returns if the slot is currently crafting or not
     */
    public function getIsCraftingAttribute() {
        return $this->recipe && $this->started_at && !$this->end_at;
    }
}
