<?php

namespace App\Models\User;

use App\Models\Model;
use App\Models\Pet\PetLevel;

class UserPetLevel extends Model {
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_pet_id', 'bonding_level', 'bonding',
    ];

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'user_pet_levels';

    /**********************************************************************************************

        RELATIONS

    **********************************************************************************************/

    /**
     * Get the pet associated with this pet stack.
     */
    public function pet() {
        return $this->belongsTo(UserPet::class, 'user_pet_id');
    }

    /**
     * Get the level associated with this pet stack.
     */
    public function level() {
        return $this->belongsTo(PetLevel::class, 'bonding_level');
    }

    /**********************************************************************************************

        ATTRIBUTES

    **********************************************************************************************/

    /**
     * Returns the level name of the pet.
     */
    public function getLevelNameAttribute() {
        return $this->level ? $this->level->name : config('lorekeeper.pets.initial_level_name');
    }

    /**
     * Gets the next level for the pet.
     */
    public function getNextLevelAttribute() {
        return PetLevel::where('level', $this->bonding_level + 1)->first();
    }
}
