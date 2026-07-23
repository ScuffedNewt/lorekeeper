<?php

namespace App\Models\Level;

use Illuminate\Database\Eloquent\Builder;

class CharacterLevel extends Level {
    public function getAssetTypeAttribute() {
        return 'character_levels';
    }

    public function getNameAttribute($value) {
        return 'Character Levels';
    }

    public function getDisplayNameAttribute() {
        return '<a href="'.url('world/levels/character').'">Character Levels</a>';
    }

    protected static function booted() {
        static::addGlobalScope('progression', function (Builder $query) {
            $query->where('level_type', 'Character')->whereNull('previous_level_id');
        });
    }
}
