<?php

namespace App\Models\Character;

use App\Models\Claymore\Experience;
use App\Models\Model;

class CharacterExperience extends Model {
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'character_id', 'experience_id', 'quantity', 'data',
    ];

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'character_experience_points';

    /**********************************************************************************************

        RELATIONS

    **********************************************************************************************/

    /**
     * Get the character.
     */
    public function character() {
        return $this->belongsTo(Character::class);
    }

    /**
     * Get the experience.
     */
    public function experience() {
        return $this->belongsTo(Experience::class, 'experience_id');
    }

    /**********************************************************************************************

        ATTRIBUTES

    **********************************************************************************************/
}
