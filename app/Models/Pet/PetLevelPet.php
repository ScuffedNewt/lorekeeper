<?php

namespace App\Models\Pet;

use App\Models\Model;

class PetLevelPet extends Model {
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'pet_level_id', 'pet_id', 'rewards',
    ];

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'pet_level_pets';

    /**********************************************************************************************

        RELATIONS

    **********************************************************************************************/

    /**
     * Get the level this pet is attached to.
     */
    public function level() {
        return $this->belongsTo(PetLevel::class, 'pet_level_id');
    }

    /**
     * Get the pets associated with this level.
     */
    public function pet() {
        return $this->belongsTo(Pet::class);
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
