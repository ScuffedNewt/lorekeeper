<?php

namespace App\Models\Claymore;

use App\Models\Currency\Currency;
use App\Models\Model;
use App\Models\User\User;
use App\Models\User\UserGear;

class Gear extends Model {
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'gear_category_id', 'name', 'has_image', 'description', 'parsed_description', 'allow_transfer',
        'parent_id', 'currency_id', 'cost',
    ];

    protected $appends = ['image_url'];

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'gears';

    /**
     * Validation rules for creation.
     *
     * @var array
     */
    public static $createRules = [
        'gear_category_id' => 'nullable',
        'name'             => 'required|unique:gears|between:3,100',
        'description'      => 'nullable',
        'image'            => 'mimes:png,webp',
    ];

    /**
     * Validation rules for updating.
     *
     * @var array
     */
    public static $updateRules = [
        'gear_category_id' => 'nullable',
        'name'             => 'required|between:3,100',
        'description'      => 'nullable',
        'image'            => 'mimes:png,webp',
    ];

    /**********************************************************************************************

        RELATIONS

    **********************************************************************************************/

    /**
     * Get the category the gear belongs to.
     */
    public function category() {
        return $this->belongsTo(GearCategory::class, 'gear_category_id');
    }

    /**
     * Get the parent of the gear.
     */
    public function parent() {
        return $this->belongsTo(self::class, 'parent_id');
    }

    /**
     * Get the children of the gear.
     */
    public function children() {
        return $this->hasMany(self::class, 'parent_id');
    }

    /**
     * Get the stats of the gear.
     */
    public function stats() {
        return $this->hasMany(GearStat::class);
    }

    /**
     * Get the currency that the parent costs.
     */
    public function currency() {
        return $this->belongsTo(Currency::class);
    }

    /**********************************************************************************************

        SCOPES

    **********************************************************************************************/

    /**
     * Scope a query to sort gears in alphabetical order.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param bool                                  $reverse
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeSortAlphabetical($query, $reverse = false) {
        return $query->orderBy('name', $reverse ? 'DESC' : 'ASC');
    }

    /**
     * Scope a query to sort gears in category order.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeSortCategory($query) {
        if (GearCategory::all()->count()) {
            return $query->orderBy(GearCategory::select('sort')->whereColumn('gear_category_id', 'gear_categories.id'), 'DESC');
        }

        return $query;
    }

    /**
     * Scope a query to sort gears by newest first.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeSortNewest($query) {
        return $query->orderBy('id', 'DESC');
    }

    /**
     * Scope a query to sort features oldest first.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeSortOldest($query) {
        return $query->orderBy('id');
    }

    /**
     * Scope a query to show only released or "released" (at least one user-owned stack has ever existed) gears.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeReleased($query) {
        return $query->whereIn('id', UserGear::pluck('gear_id')->toArray())->orWhere('is_released', 1);
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
        return '<a href="'.$this->url.'" class="display-item">'.$this->name.'</a>';
    }

    /**
     * Gets the file directory containing the model's image.
     *
     * @return string
     */
    public function getImageDirectoryAttribute() {
        return 'images/data/gear';
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
        if (!$this->has_image) {
            return null;
        }

        return asset($this->imageDirectory.'/'.$this->imageFileName);
    }

    /**
     * Gets the URL of the model's encyclopedia page.
     *
     * @return string
     */
    public function getUrlAttribute() {
        return url('world/gear?name='.$this->name);
    }

    /**
     * Gets the URL of the individual gear's page, by ID.
     *
     * @return string
     */
    public function getIdUrlAttribute() {
        return url('world/gear/'.$this->id);
    }

    /**
     * Gets the currency's asset type for asset management.
     *
     * @return string
     */
    public function getAssetTypeAttribute() {
        return 'gears';
    }

    /**
     * Gets the admin edit URL.
     *
     * @return string
     */
    public function getAdminUrlAttribute() {
        return url('admin/gear/edit/'.$this->id);
    }

    /**********************************************************************************************

        OTHER FUNCTIONS

    **********************************************************************************************/

    /**
     * displays the gear's name, with stats.
     */
    public function displayWithStats() {
        $stats = $this->stats->sortByDesc('value')->map(function ($stat) {
            return $stat->stat->name.' + '.$stat->count;
        })->implode(', ');

        return $this->name.'<br />'.($stats ? ' ('.$stats.')' : '');
    }

    /**
     * Gets the imageurl of the user's stack of this gear.
     *
     * @param mixed $id
     */
    public function getStackImageUrl($id) {
        return UserGear::find($id)->imageUrl;
    }
}
