<?php namespace App\Services;

use App\Services\Service;

use DB;
use Config;
use Settings;

use Illuminate\Support\Arr;
use App\Models\Weather\Season;
use App\Models\Weather\Weather;
use App\Models\Weather\SeasonWeather;


class WeatherService extends Service
{
    /*
    |--------------------------------------------------------------------------
    | Weather Service
    |--------------------------------------------------------------------------
    |
    | Handles the creation and editing of weather seasons.
    |
    */

    /**
     * Creates a weather season.
     *
     * @param  array  $data
     * @return bool|\App\Models\Weather\Season
     */
    public function createSeason($data)
    {
        DB::beginTransaction();

        try {

            $data = $this->populateData($data);

            $season = Season::create($data);

            $image = null;
            if(isset($data['image']) && $data['image']) {
                $data['has_image'] = 1;
                $image = $data['image'];
                unset($data['image']);
            }

            $this->populateSeason($season, Arr::only($data, ['weather_id', 'weight']));

            if ($image) $this->handleImage($image, $season->imagePath, $season->imageFileName);

            return $this->commitReturn($season);
        } catch(\Exception $e) {
            $this->setError('error', $e->getMessage());
        }
        return $this->rollbackReturn(false);
    }

    /**
     * Updates a weather season.
     *
     * @param  \App\Models\Weather\Season  $season
     * @param  array                       $data
     * @return bool|\App\Models\Weather\Season
     */
    public function updateSeason($season, $data)
    {
        DB::beginTransaction();

        try {

            $data = $this->populateData($data);

            $image = null;
            if(isset($data['image']) && $data['image']) {
                $data['has_image'] = 1;
                $image = $data['image'];
                unset($data['image']);
            }

            $season->update($data);

            if ($image) $this->handleImage($image, $season->imagePath, $season->imageFileName);

            $this->populateSeason($season, Arr::only($data, ['weather_id','weight','rewardable_type']));

            return $this->commitReturn($season);
        } catch(\Exception $e) {
            $this->setError('error', $e->getMessage());
        }
        return $this->rollbackReturn(false);
    }

    /**
     * Handles the creation of weather for a weather season.
     *
     * @param  \App\Models\Weather\Season  $season
     * @param  array                       $data
     */
    private function populateSeason($season, $data)
    {
        // Clear the old weather...
        $season->loot()->delete();

        if (isset($data['weather_id']) && $data['weather_id']) {
            foreach ($data['weather_id'] as $key => $type) {
                SeasonWeather::create([
                    'season_id'   => $season->id,
                    'weather_id'   => isset($type) ? $type : 1,
                    'weight'          => $data['weight'][$key],
                ]);
            }
        }
    }

    /**
     * Deletes a weather season.
     *
     * @param  \App\Models\Weather\Season  $season
     * @return bool
     */
    public function deleteSeason($season)
    {
        DB::beginTransaction();

        try {
            if(Settings::get('site_season') == $season->id) throw new \Exception("The site's season is currently set to this season. Change the season first.");
            
            $season->loot()->delete();
            if($season->has_image) $this->deleteImage($season->imagePath, $season->imageFileName);
            $season->delete();

            return $this->commitReturn(true);
        } catch(\Exception $e) {
            $this->setError('error', $e->getMessage());
        }
        return $this->rollbackReturn(false);
    }

    // weather

    /**
     * Creates a weather.
     *
     * @param  array  $data
     * @return bool|\App\Models\Weather\Weather
     */
    public function createWeather($data)
    {
        DB::beginTransaction();

        try {

            $data = $this->populateWeatherData($data);

            $image = null;
            if(isset($data['image']) && $data['image']) {
                $data['has_image'] = 1;
                $image = $data['image'];
                unset($data['image']);
            }
            else $data['has_image'] = 0;

            $weather = Weather::create(Arr::only($data, ['name', 'description', 'image', 'remove_image', 'is_visible', 'summary', 'disclose_rates',]));

            if ($image) $this->handleImage($image, $weather->imagePath, $weather->imageFileName);

            return $this->commitReturn($weather);
        } catch(\Exception $e) {
            $this->setError('error', $e->getMessage());
        }
        return $this->rollbackReturn(false);
    }

    /**
     * Updates a weather.
     *
     * @param  \App\Models\Weather\Weather  $weather
     * @param  array                       $data
     * @return bool|\App\Models\Weather\Weather
     */
    public function updateWeather($weather, $data)
    {
        DB::beginTransaction();

        try { 

            $data = $this->populateWeatherData($data);

            $image = null;
            if(isset($data['image']) && $data['image']) {
                $data['has_image'] = 1;
                $image = $data['image'];
                unset($data['image']);
            }


            $weather->update($data);

            if ($image) $this->handleImage($image, $weather->imagePath, $weather->imageFileName);

            return $this->commitReturn($weather);
        } catch(\Exception $e) {
            $this->setError('error', $e->getMessage());
        }
        return $this->rollbackReturn(false);
    }


    /**
     * Deletes a weather.
     *
     * @param  \App\Models\Weather\Weather  $weather
     * @return bool
     */
    public function deleteWeather($weather)
    {
        DB::beginTransaction();

        try {
            // Check first if the weather is currently in use
            if(SeasonWeather::where('weather_id', $weather->id)->exists()) throw new \Exception("A season has this weather as an option. Please remove it from the list first.");
            if(Settings::get('site_weather') == $weather->id) throw new \Exception("The site's weather is currently set to this weather. Change the weather first.");
            
            $weather->delete();
            if($weather->has_image) $this->deleteImage($weather->imagePath, $weather->imageFileName);

            return $this->commitReturn(true);
        } catch(\Exception $e) {
            $this->setError('error', $e->getMessage());
        }
        return $this->rollbackReturn(false);
    }

    /**
     * Handle weather data.
     *
     * @param  array                               $data
     * @param  \App\Models\Weather\Weather|null  $weather
     * @return array
     */
    private function populateWeatherData($data, $weather = null)
    {
        if(isset($data['description']) && $data['description']) $data['parsed_description'] = parse($data['description']);

        isset($data['is_visible']) && $data['is_visible'] ? $data['is_visible'] : $data['is_visible'] = 0;

       
        if(isset($data['remove_image']))
        {
            if($weather && $weather->has_image && $data['remove_image'])
            {
                $data['has_image'] = 0;
                $this->deleteImage($weather->imagePath, $weather->imageFileName);
            }
            unset($data['remove_image']);
        }

        return $data;
    }

    /**
     * Handle season data.
     *
     * @param  array                               $data
     * @param  \App\Models\Weather\Season|null  $season
     * @return array
     */
    private function populateData($data, $season = null)
    {
        if(isset($data['description']) && $data['description']) $data['parsed_description'] = parse($data['description']);

        isset($data['is_visible']) && $data['is_visible'] ? $data['is_visible'] : $data['is_visible'] = 0;

       
        if(isset($data['remove_image']))
        {
            if($season && $season->has_image && $data['remove_image'])
            {
                $data['has_image'] = 0;
                $this->deleteImage($season->imagePath, $season->imageFileName);
            }
            unset($data['remove_image']);
        }

        return $data;
    }
}
