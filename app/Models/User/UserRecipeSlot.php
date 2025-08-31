<?php

namespace App\Models\User;

use App\Models\Model;

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
     * Get the item associated with this item stack.
     */
    public function slot() {
        return $this->belongsTo(RecipeSlot::class, 'slot_id');
    }

    /**
     * Get the item associated with this item stack.
     */
    public function recipe() {
        return $this->belongsTo(Recipe::class, 'recipe_id');
    }
}
