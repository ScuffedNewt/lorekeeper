<?php

namespace App\Http\Controllers\Admin\Data;

use Illuminate\Http\Request;

use Auth;

use App\Models\Weather\Weather;
use App\Models\Weather\Season;
use App\Models\Item\Item;
use App\Models\Currency\Currency;
use App\Services\WeatherService;

use App\Http\Controllers\Controller;

class WeatherController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Admin / Weather Controller
    |--------------------------------------------------------------------------
    |
    | Handles creation/editing of weather and weather cycles.
    |
    */

    /**
     * Shows the season index.
     *
     */
    public function getIndex()
    {
        return view('admin.weather.seasons', [
            'seasons' => Season::orderBy('name', 'DESC')->get()
        ]);
    }

    /**
     * Shows the create season page.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getCreateSeason()
    {
        return view('admin.weather.create_edit_season', [
            'season' => new Season,
            'weathers' => Weather::orderBy('name')->pluck('name', 'id'),
        ]);
    }

    /**
     * Shows the edit season page.
     *
     * @param  int  $id
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getEditSeason($id)
    {
        $season = Season::find($id);
        if(!$season) abort(404);

        return view('admin.weather.create_edit_season', [
            'season' => $season,
            'weathers' => Weather::orderBy('name')->pluck('name', 'id'),
        ]);
    }

    /**
     * Creates or edits a season.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  App\Services\WeatherService  $service
     * @param  int|null                  $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postCreateEditSeason(Request $request, WeatherService $service, $id = null)
    { 
        $id ? $request->validate(Season::$updateRules) : $request->validate(Season::$createRules);
        $data = $request->only(['name', 'description', 'image', 'remove_image', 'is_visible', 'summary', 'weather_id', 'weight', 'start_at', 'end_at'
        ]);
        if($id && $service->updateSeason(Season::find($id), $data, Auth::user())) {
            flash('Weather updated successfully.')->success();
        }
        else if (!$id && $season = $service->createSeason($data, Auth::user())) {
            flash('Season created successfully.')->success();
            return redirect()->to('admin/weather/seasons/edit/'.$season->id);
        }
        else {
            foreach($service->errors()->getMessages()['error'] as $error) flash($error)->error();
        }
        return redirect()->back();
    }

    /**
     * Gets the season deletion modal.
     *
     * @param  int  $id
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getDeleteSeason($id)
    {
        $season = Season::find($id);
        return view('admin.weather._delete_season', [
            'season' => $season,
        ]);
    }

    /**
     * Deletes a season.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  App\Services\WeatherService  $service
     * @param  int                       $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postDeleteSeason(Request $request, WeatherService $service, $id)
    {
        if($id && $service->deleteSeason(Season::find($id))) {
            flash('Season deleted successfully.')->success();
        }
        else {
            foreach($service->errors()->getMessages()['error'] as $error) flash($error)->error();
        }
        return redirect()->to('admin/weather/seasons');
    }

    /**
     * Sorts seasons.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  App\Services\WeatherService  $service
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postSortSeason(Request $request, WeatherService $service)
    {
        if($service->sortSeason($request->get('sort'))) {
            flash('Season order updated successfully.')->success();
        }
        else {
            foreach($service->errors()->getMessages()['error'] as $error) flash($error)->error();
        }
        return redirect()->back();
    }

    /**
     * Gets the loot table test roll modal.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  App\Services\WeatherService  $service
     * @param  int                       $id
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getRollSeason(Request $request, WeatherService $service, $id)
    { 
        $table = Season::find($id);
        if(!$table) abort(404);

        // Normally we'd merge the result tables, but since we're going to be looking at
        // the results of each roll individually on this page, we'll keep them separate
        $results = [];
        for ($i = 0; $i < $request->get('quantity'); $i++)
            $results[] = $table->roll();

        return view('admin.weather._roll_season_table', [
            'table' => $table,
            'results' => $results,
            'quantity' => $request->get('quantity')
        ]);
    }

    // weather itself

    /**
     * Shows the weather index.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getWeatherIndex(Request $request)
    {
        $query = Weather::query();
        $data = $request->only(['name']);

        if(isset($data['name'])) {
            $query->where('name', 'LIKE', '%'.$data['name'].'%');
        }
        return view('admin.weather.weather', [
            'weathers' =>  $query->paginate(20)->appends($request->query()),
        ]);
    }

    /**
     * Shows the create weather page.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getCreateWeather()
    {
        return view('admin.weather.create_edit_weather', [
            'weather' => new Weather,
            'weathers' => Weather::orderBy('name')->pluck('name', 'id'),
        ]);
    }

    /**
     * Shows the edit weather page.
     *
     * @param  int  $id
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getEditWeather($id)
    {
        $weather = Weather::find($id);
        if(!$weather) abort(404);

        return view('admin.weather.create_edit_weather', [
            'weather' => $weather,
            'weathers' => Weather::orderBy('name')->pluck('name', 'id'),
        ]);
    }

    /**
     * Creates or edits a weather.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  App\Services\WeatherService  $service
     * @param  int|null                  $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postCreateEditWeather(Request $request, WeatherService $service, $id = null)
    {
        $id ? $request->validate(Weather::$updateRules) : $request->validate(Weather::$createRules);
        $data = $request->only([
            'name', 'description', 'image', 'remove_image', 'is_visible', 'summary',
        ]);
        if($id && $service->updateWeather(Weather::find($id), $data, Auth::user())) {
            flash('Weather updated successfully.')->success();
        }
        else if (!$id && $weather = $service->createWeather($data, Auth::user())) {
            flash('Weather created successfully.')->success();
            return redirect()->to('admin/weather/weathers/edit/'.$weather->id);
        }
        else {
            foreach($service->errors()->getMessages()['error'] as $error) flash($error)->error();
        }
        return redirect()->back();
    }

    /**
     * Gets the weather deletion modal.
     *
     * @param  int  $id
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getDeleteWeather($id)
    {
        $weather = Weather::find($id);
        return view('admin.weather._delete_weather', [
            'weather' => $weather,
        ]);
    }

    /**
     * Deletes a weather.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  App\Services\WeatherService  $service
     * @param  int                       $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postDeleteWeather(Request $request, WeatherService $service, $id)
    {
        if($id && $service->deleteWeather(Weather::find($id))) {
            flash('Weather deleted successfully.')->success();
        }
        else {
            foreach($service->errors()->getMessages()['error'] as $error) flash($error)->error();
        }
        return redirect()->to('admin/weather/weathers');
    }

    /**
     * Sorts weathers.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  App\Services\WeatherService  $service
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postSortWeather(Request $request, WeatherService $service)
    {
        if($service->sortWeather($request->get('sort'))) {
            flash('Weather order updated successfully.')->success();
        }
        else {
            foreach($service->errors()->getMessages()['error'] as $error) flash($error)->error();
        }
        return redirect()->back();
    }

}