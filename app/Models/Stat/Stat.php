<?php

namespace App\Models\Stat;

use App\Models\Claymore\Gear;
use App\Models\Claymore\Weapon;
use App\Models\Model;
use App\Models\Species\SpeciesLimit;

class Stat extends Model {
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'abbreviation', 'base', 'increment', 'multiplier', 'max_level', 'colour', 'data',
    ];

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'stats';

    /**
     * Validation rules for creation.
     *
     * @var array
     */
    public static $createRules = [
        'name'         => 'required|unique:stats|between:3,25',
        'abbreviation' => 'unique:stats|between:1,10',
    ];

    /**
     * Validation rules for updating.
     *
     * @var array
     */
    public static $updateRules = [
        'name'         => 'required|between:3,25',
        'abbreviation' => 'between:1,10',
    ];

    /**********************************************************************************************

        RELATIONS

    **********************************************************************************************/

    /**
     * get the species limits for the stat.
     */
    public function limits() {
        return $this->hasMany(SpeciesLimit::class, 'type_id')->where('type', 'stat');
    }

    /**********************************************************************************************

        ACCESSORS

    **********************************************************************************************/

    /**
     * Displays the model's name, linked to its encyclopedia page.
     *
     * @return string
     */
    public function getDisplayNameAttribute() {
        return '<a href="'.$this->url.'" '.($this->colour ? 'style="color: '.$this->colour.' !important;"' : '').'>'.$this->name.' Stat </a>';
    }

    /**
     * Displays the model's name, linked to its encyclopedia page.
     *
     * @return string
     */
    public function getUrlAttribute() {
        return url('world/stats/'.$this->abbreviation);
    }

    /**
     * Gets all the equipment (gear or weapons) that modify this stat.
     */
    public function getEquipmentAttribute() {
        $gear = Gear::whereHas('stats', function ($query) {
            $query->where('stat_id', $this->id);
        })->get();

        $weapons = Weapon::whereHas('stats', function ($query) {
            $query->where('stat_id', $this->id);
        })->get();

        return $gear->merge($weapons);
    }

    /**
     * Gets the admin edit URL.
     *
     * @return string
     */
    public function getAdminUrlAttribute() {
        return url('admin/stats/edit/'.$this->id);
    }

    /**
     * Gets the power required to edit this model.
     *
     * @return string
     */
    public function getAdminPowerAttribute() {
        return 'edit_claymores';
    }

    /**********************************************************************************************

        OTHER FUNCTIONS

    **********************************************************************************************/

    /**
     * Displays the species and subtype restrictions for the stat.
     *
     * @param mixed $is_flat
     */
    public function displayLimits($is_flat = false) {
        if (!count($this->limits)) {
            return null;
        }

        $species = $this->limits->where('is_subtype', 0)->map(function ($limit) {
            return $limit->limit->displayName;
        })->toArray();

        $subtypes = $this->limits->where('is_subtype', 1)->map(function ($limit) {
            return $limit->limit->displayName.' ('.$limit->limit->species->name.')';
        })->toArray();

        return '<b>Species:</b> '.($species ? implode(', ', $species) : 'None').($is_flat ? ', ' : '<br>').'<b>Subtypes:</b> '.($subtypes ? implode(', ', $subtypes) : 'None');
    }

    /**
     * Gets the stat's data as an object.
     */
    public function getDataAttribute() {
        return json_decode($this->attributes['data'], true);
    }

    /**
     * Checks if a certain species / subtype has a different base value.
     *
     * @param mixed $type
     * @param mixed $id
     */
    public function hasBaseValue($type, $id) {
        if (!isset($this->data['bases'])) {
            return false;
        }

        if (!isset($this->data['bases'][$type]) || !isset($this->data['bases'][$type][$id])) {
            return false;
        }

        return $this->data['bases'][$type][$id];
    }
}
