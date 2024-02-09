<?php

namespace App\Models\Character;

use App\Models\Model;

class CharacterNpcInformation extends Model {
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'character_id', 'biography', 'default_affection',
    ];

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'character_npc_information';

    /**
     * The primary key of the model.
     *
     * @var string
     */
    public $primaryKey = 'character_id';

    /**********************************************************************************************

        RELATIONS

    **********************************************************************************************/

    /**
     * Get the character this profile belongs to.
     */
    public function character() {
        return $this->belongsTo(Character::class, 'character_id');
    }
}
