<?php

namespace App\Models\Weather;

use App\Models\Model;

class ObjectWeather extends Model {
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'object_model', 'object_id', 'weathers', 'active_weathers', 'reset_period', 'use_season_weather', 'is_hidden', 'data',
    ];

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'object_weather';

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'weathers'        => 'array',
        'active_weathers' => 'array',
        'data'            => 'array',
    ];

    /**********************************************************************************************

        RELATIONS

    **********************************************************************************************/

    /**
     * Gets the object that the weather is linked to.
     */
    public function object() {
        return $this->belongsTo($this->object_model, 'object_id');
    }

    /**********************************************************************************************

        ATTRIBUTES

    **********************************************************************************************/

    /**
     * Returns the weathers for the object as a Weather model.
     */
    public function getWeather() {
        if ($this->use_season_weather && !$this->is_hidden) {
            $season = getSiteWeather()['season'];
            if ($season) {
                $seasonWeather = $season->weather()->get();
            }
        } else {
            $seasonWeather = collect();
        }
        $modelWeather = Weather::whereIn('id', array_keys($this->weathers))->get();

        return $modelWeather->merge($seasonWeather);
    }

    /**
     * Returns the active weathers for the object as a Weather model.
     */
    public function getActiveWeather() {
        return Weather::whereIn('id', $this->active_weathers)->get();
    }

    /**********************************************************************************************

        OTHER FUNCTIONS

    **********************************************************************************************/

    /**
     * Returns the weather for the object as a message.
     */
    public function getWeatherMessage() {
        $weathers = $this->getWeather();
        if (!count($weathers)) {
            return 'No weather set.';
        }

        return implode(', ', $weathers->pluck('displayName')->toArray());
    }

    /**
     * Returns the active weather for the object as a message.
     */
    public function getActiveWeatherMessage() {
        $weathers = $this->getWeather();
        if (!count($weathers)) {
            return 'No weather set.';
        }

        $message = [];
        foreach ($weathers as $weather) {
            if ($this->isWeatherActive($weather->id)) {
                $message[] = $weather->has_image ? '<img src="'.$weather->imageUrl.'" style="width: 25px; height: 25px" />'.$weather->displayName : $weather->displayName;
            }
        }

        if (!count($message)) {
            return 'No active weather.';
        }

        return implode(', ', $message);
    }

    /**
     * returns if a specific weather is active or not.
     *
     * @param mixed $id
     */
    public function isWeatherActive($id) {
        return in_array($id, $this->active_weathers);
    }

    /**
     * Changes the current active weather for the object.
     */
    public function changeWeather() {
        $availableWeathers = [];
        $totalWeight = 0;
        foreach ($this->weathers as $id=>$weight) {
            $availableWeathers[$id] = $weight;
            $totalWeight += $weight;
        }

        if ($this->use_season_weather) {
            $season = getSiteWeather()['season'];
            if ($season) {
                foreach ($season->weather as $weather) {
                    $availableWeathers[$weather->id] = $weather->weight;
                    $totalWeight += $weather->weight;
                }
            }
        }

        // shuffle the array to get a random order
        shuffle($availableWeathers);

        $chosen_weather = [];
        $min_selected = $this->data['min_weather_selected'] ?? 1;
        $max_selected = $this->data['max_weather_selected'] ?? 1;
        $selected = mt_rand($min_selected, $max_selected);
        for ($i = 0; $i < $selected; $i++) {
            if ($totalWeight == 0) {
                continue;
            }
            $roll = mt_rand(0, $totalWeight - 1);
            $result = null;
            $prev = null;
            $count = 0;
            foreach ($availableWeathers as $id=>$weight) {
                $count += $weight;

                if ($roll < $count) {
                    $result = $id;
                    break;
                }
                $prev = $id;
            }
            if (!$result) {
                $result = $prev;
            }
            $chosen_weather[] = $result;
        }

        $this->active_weathers = $chosen_weather;
        $this->save();
    }
}
