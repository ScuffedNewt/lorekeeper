<?php

namespace App\Models\User;

use App\Models\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class UserCraftingSlot extends Model
{

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'slot_id', 'user_id', 'recipe_id', 'started_at'
    ];

    /**
     * Whether the model contains timestamps to be saved and updated.
     *
     * @var string
     */
    public $timestamps = ['started_at'];

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'user_crafting_slots';

    /**********************************************************************************************
    
        RELATIONS

    **********************************************************************************************/

    /**
     * Get the user who owns the stack.
     */
    public function user() 
    {
        return $this->belongsTo('App\Models\User\User');
    }

    /**
     * Get the item associated with this item stack.
     */
    public function slot() 
    {
        return $this->belongsTo('App\Models\Recipe\CraftingSlot');
    }

    /**
     * Get the item associated with this item stack.
     */
    public function recipe() 
    {
        return $this->belongsTo('App\Models\Recipe\Recipe');
    }

}
