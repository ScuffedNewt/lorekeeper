<?php

namespace App\Models\Level;

use Illuminate\Database\Eloquent\Builder;

class CharacterSpecificLevel extends Level {
    /**********************************************************************************************

        ATTRIBUTES

    **********************************************************************************************/
    public function getAssetTypeAttribute() {
        return 'character_specific_levels';
    }

    /**********************************************************************************************

        OTHER FUNCTIONS

    **********************************************************************************************/
    protected static function booted() {
        static::addGlobalScope('progression', function (Builder $query) {
            $query->where('level_type', 'Character');
        });
    }
}
