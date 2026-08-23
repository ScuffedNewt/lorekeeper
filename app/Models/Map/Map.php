<?php

namespace App\Models\Map;

use App\Models\Model;

class Map extends Model {
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'description', 'is_active',
    ];

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'maps';

    /**
     * Validation rules for creation.
     *
     * @var array
     */
    public static $createRules = [
        'name'        => 'required|unique:items|between:3,100',
        'description' => 'nullable',
        'image'       => 'mimes:png,jpg,jpeg,gif',
    ];

    /**
     * Validation rules for updating.
     *
     * @var array
     */
    public static $updateRules = [
        'name'        => 'required|between:3,100',
        'description' => 'nullable',
        'image'       => 'mimes:png,jpg,jpeg,gif',
    ];

    /**********************************************************************************************

        RELATIONS

    **********************************************************************************************/

    /**
     * Get the locations that belong to the map.
     */
    public function locations() {
        return $this->hasMany('App\Models\Map\MapLocation');
    }

    /**********************************************************************************************

        SCOPES

    **********************************************************************************************/

    /**
     * Scope a query to retrieve only active tags.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActive($query) {
        return $query->where('is_active', 1);
    }

    /**********************************************************************************************

        ACCESSORS

    **********************************************************************************************/
    /**********************************************************************************************

        ACCESSORS

    **********************************************************************************************/

    /**
     * Gets the file directory containing the model's image.
     *
     * @return string
     */
    public function getImageDirectoryAttribute() {
        return 'images/data/maps';
    }

    /**
     * Gets the file name of the model's image.
     *
     * @return string
     */
    public function getImageFileNameAttribute() {
        return $this->id.'-image.png';
    }

    /**
     * Gets the path to the file directory containing the model's image.
     *
     * @return string
     */
    public function getImagePathAttribute() {
        return public_path($this->imageDirectory);
    }

    /**
     * Gets the URL of the model's image.
     *
     * @return string
     */
    public function getImageUrlAttribute() {
        return asset($this->imageDirectory.'/'.$this->imageFileName);
    }

    /**
     * Gets the URL of the tag's editing page.
     *
     * @return string
     */
    public function getUrlAttribute() {
        return url('world/maps/'.$this->name);
    }

    /**
     * Gets the display.
     *
     * @return string
     */
    public function getDisplayAttribute() {
        $locations = [];
        foreach ($this->locations as $location) {
            $locations[] =
             '<area target="" alt="'.$location->name.'" title="'.$location->name.'"'
             .($location->link_type == 'GET' ? ' href="'.$location->link.'"' : '').
             ' data-toggle="tooltip" class="tooltip-bot" href="'.$location->url.'" coords="'.$location->cords.'" shape="'.$location->shape.'" data-original-title="'.$location->name.'">';
        }

        return
            ('<img src="'.$this->imageUrl.'" id="Image-Maps-Com-process-map" class="img-fluid border" usemap="#'.$this->name.'">')
            .('<map name="'.$this->name.'" class="image-map">')
            .(implode('', $locations).'</map>');
    }
}
