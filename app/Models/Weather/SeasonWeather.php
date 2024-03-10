<?php

namespace App\Models\Weather;

use Config;
use App\Models\Model;

class SeasonWeather extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'season_id', 'weather_id','weight'
    ];

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'season_weathers';

    /**
     * Validation rules for creation.
     *
     * @var array
     */
    public static $createRules = [
        'season_id' => 'required',
        'weight' => 'required|integer|min:1',
    ];

    /**
     * Validation rules for updating.
     *
     * @var array
     */
    public static $updateRules = [
        'season_id' => 'required',
        'weight' => 'required|integer|min:1',
    ];

    /**********************************************************************************************

        RELATIONS

    **********************************************************************************************/

    /**
     * Get the weather attached to the entry.
     */
    public function weather()
    {
        return $this->belongsTo(Weather::class, 'weather_id');
    }

    /**********************************************************************************************

        ACCESSORS

    **********************************************************************************************/

    /**
     * Display the loot item and link to it's encylopedia entry.
     *
     * @return string
     */
    public function getDisplayNameAttribute()
    {
        return '<a href="'.$this->weather->url.'">'.$this->weather->name.'</a>';
    }

    /**
     * Displays the drop rate of a loot.
     *
     * @return string
     */
    public function getDropRateAttribute()
    {
        $totalWeight = SeasonWeather::where('season_id', $this->season_id)->sum('weight');
        $dropRate = $this->weight / $totalWeight * 100;
        return number_format((float) $dropRate, 2, '.', '').'%';
    }
}
