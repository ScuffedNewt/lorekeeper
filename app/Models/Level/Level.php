<?php

namespace App\Models\Level;

use App\Models\Model;

class Level extends Model {
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'level', 'exp_required', 'stat_points', 'description', 'level_type',
    ];

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'levels';

    /**
     * Validation rules for creation.
     *
     * @var array
     */
    public static $createRules = [
        'level' => 'required',
    ];

    /**
     * Validation rules for updating.
     *
     * @var array
     */
    public static $updateRules = [
        'level' => 'required',
    ];

    /**********************************************************************************************

        RELATIONS

    **********************************************************************************************/

    /**
     * Get the rewards attached to this level.
     */
    public function rewards() {
        return $this->hasMany(LevelReward::class, 'level_id');
    }

    /**
     * Get the next level.
     */
    public function nextLevel() {
        return $this->hasOne(self::class, 'level', 'level')->where('level', $this->level + 1)->where('level_type', $this->level_type);
    }

    /**********************************************************************************************

        ATTRIBUTES

    **********************************************************************************************/

    /**
     * Displays the model's name, linked to its encyclopedia page.
     *
     * @return string
     */
    public function getDisplayNameAttribute() {
        return '<a href="'.url('world/levels').'/'.strtolower($this->level_type).'" class="display-prompt">'.ucfirst($this->level_type).' Level '.$this->level.'</a>';
    }

    /**
     * Gets the admin edit URL.
     *
     * @return string
     */
    public function getAdminUrlAttribute() {
        return url('admin/levels/'.strtolower($this->level_type).'/edit/'.$this->id);
    }

    /**
     * Gets the power required to edit this model.
     *
     * @return string
     */
    public function getAdminPowerAttribute() {
        return 'edit_claymores';
    }
}
