<?php

namespace App\Models\Recipe;

use App\Models\Model;

class CraftingSlot extends Model {
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'currency_id', 'free', 'slot_cost',
    ];

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'crafting_slots';

    /**
     * Whether the model contains timestamps to be saved and updated.
     *
     * @var string
     */
    public $timestamps = false;

    /**********************************************************************************************

        RELATIONS

    **********************************************************************************************/

    /**
     * Get the user who owns the stack.
     */
    public function currency() {
        return $this->belongsTo('App\Models\Currency\Currency');
    }
}
