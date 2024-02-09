<?php

namespace App\Models\User;

use App\Models\Model;
use App\Models\Character\Character;

class UserNpcAffection extends Model {

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'character_id', 'user_id', 'affection',
    ];

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'user_npc_affection';

    /**
     * The primary key of the model.
     *
     * @var string
     */
    public $primaryKey = 'user_id';

    /**********************************************************************************************

        RELATIONS

    **********************************************************************************************/

    /**
     * Get the character this belongs to.
     */
    public function character() {
        return $this->belongsTo(Character::class);
    }

    /**
     * Get the user this belongs to.
     */
    public function user() {
        return $this->belongsTo(User::class);
    }
}
