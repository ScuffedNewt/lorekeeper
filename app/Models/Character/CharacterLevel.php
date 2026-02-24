<?php

namespace App\Models\Character;

use App\Models\Level\Level;
use App\Models\Model;

class CharacterLevel extends Model {
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'character_id', 'level_id', 'stat_points',
    ];

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'character_levels';

    /**********************************************************************************************

        RELATIONS

    **********************************************************************************************/

    /**
     * Get the shop stock.
     */
    public function character() {
        return $this->belongsTo(Character::class);
    }

    /**
     * Get the current level for the character.
     */
    public function level() {
        return $this->belongsTo(Level::class, 'level_id', 'id')->where('level_type', 'Character')
            ->with('nextLevel')
            ->withDefault(function ($level) {
                return Level::where('level_type', 'Character')
                    ->whereNull('previous_level_id')
                    ->first();
            });
    }

    /**
     * Get the experience points for the character level.
     */
    public function experience() {
        return $this->hasOne(CharacterExperience::class, 'character_id', 'character_id')
            ->where('experience_id', config('lorekeeper.claymores_and_companions.levels.experience_id.characters'));
    }

    /**********************************************************************************************

        ATTRIBUTES

    **********************************************************************************************/

    /**
     * get the next level.
     */
    public function getNextLevelAttribute() {
        return $this->level ? $this->level->nextLevel : null;
    }

    /**
     * Calculates the width of the progress bar for the level.
     */
    public function getProgressBarWidthAttribute() {
        $nextLevel = $this->nextLevel;
        if (!$nextLevel) {
            return 100;
        }

        return ($this->experience?->quantity / $nextLevel->exp_required) * 100;
    }
}
