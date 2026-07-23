<?php

namespace App\Models\Level;

use Illuminate\Database\Eloquent\Builder;

class UserLevel extends Level {
    /**********************************************************************************************

        ATTRIBUTES

    **********************************************************************************************/

    public function getAssetTypeAttribute() {
        return 'user_levels';
    }

    public function getNameAttribute($value) {
        return 'User Levels';
    }

    public function getDisplayNameAttribute() {
        return '<a href="'.url('world/levels/user').'">User Levels</a>';
    }

    /**********************************************************************************************

        OTHER FUNCTIONS

    **********************************************************************************************/

    protected static function booted() {
        static::addGlobalScope('progression', function (Builder $query) {
            $query->where('level_type', 'User')->whereNull('previous_level_id');
        });
    }
}
