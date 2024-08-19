<?php

namespace App\Services;

use App\Facades\Settings;
use App\Models\Weather\ObjectWeather;
use App\Models\Weather\Season;
use App\Models\Weather\SeasonWeather;
use App\Models\Weather\Weather;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class WeatherService extends Service {
    /*
    |--------------------------------------------------------------------------
    | Weather Service
    |--------------------------------------------------------------------------
    |
    | Handles the creation and editing of weather seasons.
    |
    */

    /**********************************************************************************************

        SEASONS

    **********************************************************************************************/

    /**
     * Creates a season.
     *
     * @param array $data
     *
     * @return bool|Season
     */
    public function createSeason($data) {
        DB::beginTransaction();

        try {
            $data = $this->populateData($data);

            $season = Season::create($data);

            $image = null;
            if (isset($data['image']) && $data['image']) {
                $data['has_image'] = 1;
                $image = $data['image'];
                unset($data['image']);
            }

            $this->populateSeason($season, Arr::only($data, ['weather_id', 'weight']));

            if ($image) {
                $this->handleImage($image, $season->imagePath, $season->imageFileName);
            }

            return $this->commitReturn($season);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }

    /**
     * Updates a season.
     *
     * @param Season $season
     * @param array  $data
     *
     * @return bool|Season
     */
    public function updateSeason($season, $data) {
        DB::beginTransaction();

        try {
            $data = $this->populateData($data);

            $image = null;
            if (isset($data['image']) && $data['image']) {
                $data['has_image'] = 1;
                $image = $data['image'];
                unset($data['image']);
            }

            $season->update($data);

            if ($image) {
                $this->handleImage($image, $season->imagePath, $season->imageFileName);
            }

            $this->populateSeason($season, Arr::only($data, ['weather_id', 'weight', 'rewardable_type']));

            return $this->commitReturn($season);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }

    /**
     * Deletes a season.
     *
     * @param Season $season
     *
     * @return bool
     */
    public function deleteSeason($season) {
        DB::beginTransaction();

        try {
            if (Settings::get('site_season') == $season->id) {
                throw new \Exception("The site's season is currently set to this season. Change the season first.");
            }

            $season->weather()->delete();
            if ($season->has_image) {
                $this->deleteImage($season->imagePath, $season->imageFileName);
            }
            $season->delete();

            return $this->commitReturn(true);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }

    /**********************************************************************************************

        WEATHER

    **********************************************************************************************/

    /**
     * Creates a weather.
     *
     * @param array $data
     *
     * @return bool|Weather
     */
    public function createWeather($data) {
        DB::beginTransaction();

        try {
            $data = $this->populateWeatherData($data);

            $image = null;
            if (isset($data['image']) && $data['image']) {
                $data['has_image'] = 1;
                $image = $data['image'];
                unset($data['image']);
            } else {
                $data['has_image'] = 0;
            }

            $weather = Weather::create(Arr::only($data, ['name', 'description', 'image', 'remove_image', 'is_visible', 'summary', 'disclose_rates']));

            if ($image) {
                $this->handleImage($image, $weather->imagePath, $weather->imageFileName);
            }

            return $this->commitReturn($weather);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }

    /**
     * Updates a weather.
     *
     * @param Weather $weather
     * @param array   $data
     *
     * @return bool|Weather
     */
    public function updateWeather($weather, $data) {
        DB::beginTransaction();

        try {
            $data = $this->populateWeatherData($data);

            $image = null;
            if (isset($data['image']) && $data['image']) {
                $data['has_image'] = 1;
                $image = $data['image'];
                unset($data['image']);
            }

            $weather->update($data);

            if ($image) {
                $this->handleImage($image, $weather->imagePath, $weather->imageFileName);
            }

            return $this->commitReturn($weather);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }

    /**
     * Deletes a weather.
     *
     * @param Weather $weather
     *
     * @return bool
     */
    public function deleteWeather($weather) {
        DB::beginTransaction();

        try {
            // Check first if the weather is currently in use
            if (SeasonWeather::where('weather_id', $weather->id)->exists()) {
                throw new \Exception('A season has this weather as an option. Please remove it from the list first.');
            }
            if (Settings::get('site_weather') == $weather->id) {
                throw new \Exception("The site's weather is currently set to this weather. Change the weather first.");
            }

            $weather->delete();
            if ($weather->has_image) {
                $this->deleteImage($weather->imagePath, $weather->imageFileName);
            }

            return $this->commitReturn(true);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }

    /**********************************************************************************************

        OBJECT WEATHER

    **********************************************************************************************/

    /**
     * Creates a new weather object for an object.
     *
     * @param mixed $object_model
     * @param mixed $object_id
     * @param mixed $data
     * @param mixed $user
     */
    public function createObjectWeather($object_model, $object_id, $data, $user) {
        DB::beginTransaction();

        try {
            // 'weather_ids', 'weight', 'reset_period'
            if (!isset($data['weather_ids']) || !$data['weather_ids']) {
                throw new \Exception('No weather provided.');
            }
            // check that there is not duplicate element ids
            if (count($data['weather_ids']) != count(array_unique($data['weather_ids']))) {
                throw new \Exception('Duplicate weather provided.');
            }
            // check that a object weater with this model and id doesn't already exist
            if (ObjectWeather::where('object_model', $object_model)->where('object_id', $object_id)->exists()) {
                throw new \Exception('A weather object with this model and id already exists.');
            }

            // match false with all weather_ids
            $active_weather = [];
            $weather_ids = [];
            foreach ($data['weather_ids'] as $key=>$weather_id) {
                $weather_ids[$weather_id] = $data['weight'][$key] ?? 1;
                if ($data['active'][$key]) {
                    $active_weather[] = $weather_id;
                }
            }

            // create the weatherobject
            $objectWeather = ObjectWeather::create([
                'object_model'    => $object_model,
                'object_id'       => $object_id,
                'weathers'        => $weather_ids,
                'active_weathers' => $active_weather,
                'reset_period'    => $data['reset_period'] ?? null,
            ]);

            // log the action
            if (!$this->logAdminAction(Auth::user(), 'Created Weather Object', 'Created '.$objectWeather->object->displayName.'\s weather.')) {
                throw new \Exception('Failed to log admin action.');
            }

            return $this->commitReturn($objectWeather);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }

    /**
     * edits an existing weather object on a model.
     *
     * @param mixed $weatherObject
     * @param mixed $data
     * @param mixed $user
     */
    public function editObjectWeather($weatherObject, $data, $user) {
        DB::beginTransaction();

        try {
            if (!isset($data['weather_ids']) || !$data['weather_ids']) {
                throw new \Exception('No weather provided.');
            }
            // check that there is not duplicate element ids
            if (count($data['weather_ids']) != count(array_unique($data['weather_ids']))) {
                throw new \Exception('Duplicate weather provided.');
            }
            // check that a weatherobject with this model and id doesn't already exist
            if (ObjectWeather::where('object_model', $weatherObject->object_model)->where('object_id', $weatherObject->object_id)->where('id', '!=', $weatherObject->id)->exists()) {
                throw new \Exception('An Object Weather with this model and id already exists.');
            }

            $active_weather = [];
            $weather_ids = [];
            foreach ($data['weather_ids'] as $key=>$weather_id) {
                $weather_ids[$weather_id] = $data['weight'][$key] ?? 1;
                if ($data['active'][$key]) {
                    $active_weather[] = $weather_id;
                }
            }

            // create the weatherobject
            $weatherObject->update([
                'weathers'        => $weather_ids,
                'active_weathers' => $active_weather,
                'reset_period'    => $data['reset_period'] ?? null,
            ]);

            // log the action
            if (!$this->logAdminAction(Auth::user(), 'Edited Weather Object', 'Edited '.$weatherObject->object->displayName.'\s weather.')) {
                throw new \Exception('Failed to log admin action.');
            }

            return $this->commitReturn($weatherObject);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }

    /**
     * deletes a weather object.
     *
     * @param mixed $weatherObject
     */
    public function deleteObjectWeather($weatherObject) {
        DB::beginTransaction();

        try {
            // log the action
            if (!$this->logAdminAction(Auth::user(), 'Deleted Weather Object', 'Deleted '.$weatherObject->object->displayName.'\'s weather.')) {
                throw new \Exception('Failed to log admin action.');
            }

            $weatherObject->delete();

            return $this->commitReturn($weatherObject);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }

    /**
     * Handles the creation of weather for a season.
     *
     * @param Season $season
     * @param array  $data
     */
    private function populateSeason($season, $data) {
        // Clear the old weather...
        $season->weather()->delete();

        if (isset($data['weather_id']) && $data['weather_id']) {
            foreach ($data['weather_id'] as $key => $type) {
                SeasonWeather::create([
                    'season_id'       => $season->id,
                    'weather_id'      => $type ?? 1,
                    'weight'          => $data['weight'][$key],
                ]);
            }
        }
    }

    /**
     * Handle weather data.
     *
     * @param array        $data
     * @param Weather|null $weather
     *
     * @return array
     */
    private function populateWeatherData($data, $weather = null) {
        if (isset($data['description']) && $data['description']) {
            $data['parsed_description'] = parse($data['description']);
        }

        isset($data['is_visible']) && $data['is_visible'] ? $data['is_visible'] : $data['is_visible'] = 0;

        if (isset($data['remove_image'])) {
            if ($weather && $weather->has_image && $data['remove_image']) {
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
     * @param array       $data
     * @param Season|null $season
     *
     * @return array
     */
    private function populateData($data, $season = null) {
        if (isset($data['description']) && $data['description']) {
            $data['parsed_description'] = parse($data['description']);
        }

        isset($data['is_visible']) && $data['is_visible'] ? $data['is_visible'] : $data['is_visible'] = 0;

        if (isset($data['remove_image'])) {
            if ($season && $season->has_image && $data['remove_image']) {
                $data['has_image'] = 0;
                $this->deleteImage($season->imagePath, $season->imageFileName);
            }
            unset($data['remove_image']);
        }

        return $data;
    }
}
