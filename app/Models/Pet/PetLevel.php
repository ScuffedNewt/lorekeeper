<?php

namespace App\Models\Pet;

use App\Models\Model;

class PetLevel extends Model {
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'level', 'bonding_required', 'rewards',
    ];

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'pet_levels';

    /**********************************************************************************************

        RELATIONS

    **********************************************************************************************/

    /**
     * Get the pets associated with this level.
     */
    public function pets() {
        return $this->hasMany(PetLevelPet::class);
    }

    /**********************************************************************************************

        ATTRIBUTES

    **********************************************************************************************/

    /**
     * Returns rewards as objects.
     */
    public function getRewardsAttribute() {
        if (empty($this->attributes['rewards'])) {
            return [];
        }
        $rewards = [];
        foreach (json_decode($this->attributes['rewards']) as $key=>$reward) {
            if (count(json_decode($this->attributes['rewards'], true)[$key])) {
                $rewards[] = $reward;
            }
        }

        return $rewards;
    }
}
