<?php

namespace App\Models\Garden;

use App\Models\Model;

class GardenObject extends Model {
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'type', 'object_model', 'object_id', 'data',
    ];

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'garden_objects';

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'data' => 'array',
    ];

    /**********************************************************************************************

        RELATIONS

    **********************************************************************************************/

    /**
     * Get the user who owns the stack.
     */
    public function object() {
        return $this->belongsTo($this->object_model, 'object_id');
    }

    /**********************************************************************************************

        ATTRIBUTES

    **********************************************************************************************/

    /**
     * Get the required quality attribute.
     */
    public function getRequiredQualityAttribute() {
        return $this->data['quality'] ?? null;
    }

    /**
     * Displays the objects's rewards.
     *
     * @return string
     */
    public function getRewardsAttribute() {
        if (!isset($this->data['rewards'])) {
            return [];
        }

        $assets = parseAssetData($this->data['rewards']);
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

    // TODO: Potential rewards implementation
    // /**
    //  * Displays the object's potential rewards.
    //  *
    //  * @return string
    //  */
    // public function getPotentialRewardsAttribute() {
    //     if (!isset($this->data['potential_rewards'])) {
    //         return [];
    //     }

    //     $assets = parseAssetData($this->data['potential_rewards']);
    //     $rewards = [];
    //     foreach ($assets as $type => $a) {
    //         $class = getAssetModelString($type, false);
    //         foreach ($a as $id => $asset) {
    //             $rewards[] = (object) [
    //                 'rewardable_type' => $class,
    //                 'rewardable_id'   => $id,
    //                 'quantity'        => $asset['quantity'],
    //                 'chance'          => $asset['data']['chance'] ?? 0,
    //             ];
    //         }
    //     }

    //     return $rewards;
    // }

    /**********************************************************************************************

        OTHER FUNCTIONS

    **********************************************************************************************/

    /**
     * Displays the object's stage images.
     *
     * @param mixed $interactions
     */
    public function stageImage($interactions) {
        // find closest stage image less than or equal to current stage
        // stage's that have images are stored in data['stages']
        if (!isset($this->data['stages']) || !is_array($this->data['stages'])) {
            return null;
        }

        if (isset($this->data['stages'][$interactions])) {
            return asset('images/data/gardens/objects/'.$this->id.'-'.$interactions.'-image.png');
        }

        $stages = collect($this->data['stages'])->filter(function ($value, $key) use ($interactions) {
            return $key < $interactions;
        })->sortKeysDesc();

        if ($stages->isNotEmpty()) {
            $closestStage = $stages->keys()->first();

            return asset('images/data/gardens/objects/'.$this->id.'-'.$closestStage.'-image.png');
        }

        return null;
    }
}
