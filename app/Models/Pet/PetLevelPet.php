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

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'rewards' => 'array',
    ];

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
     * Get the rewards for the submission/claim.
     *
     * @return array
     */
    public function getRewardDataAttribute() {
        if (!$this->rewards) {
            return [];
        }

        $assets = parseAssetData($this->rewards);
        $rewards = [];
        foreach ($assets as $type => $a) {
            $class = getAssetModelString($type, false);
            foreach ($a as $id => $asset) {
                $rewards[] = (object) [
                    'rewardable_type' => $class,
                    'rewardable_id'   => $id,
                    'quantity'        => $asset['quantity'],
                ];
            }
        }

        return $rewards;
    }
}
