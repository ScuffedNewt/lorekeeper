<?php

namespace App\Models\User;

use App\Models\Character\Character;
use App\Models\Level\Level;
use App\Models\Model;

class UserLevel extends Model {
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id', 'level_id', 'stat_points', 'stamina', 'character_id',
    ];

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'user_levels';

    /**********************************************************************************************

        RELATIONS

    **********************************************************************************************/

    /**
     * Get the user.
     */
    public function user() {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the attached character.
     */
    public function character() {
        return $this->belongsTo(Character::class);
    }

    /**
     * Get the current level.
     */
    public function level() {
        return $this->hasOne(Level::class, 'id', 'level_id')->where('level_type', 'User')
            ->with('nextLevel')
            ->withDefault(function ($level) {
                return Level::where('level_type', 'User')
                    ->whereNull('previous_level_id')
                    ->first();
            });
    }

    /**
     * Get the experience points for the user level.
     */
    public function experience() {
        return $this->hasOne(UserExperience::class, 'user_id', 'user_id')
            ->where('experience_id', config('lorekeeper.claymores_and_companions.levels.experience_id.users'));
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
     * Get current stamina as a progress bar width.
     */
    public function getStaminaProgressAttribute() {
        return ($this->stamina / 15) * 100;
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
