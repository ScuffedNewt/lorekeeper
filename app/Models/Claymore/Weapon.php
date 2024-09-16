<?php

namespace App\Models\Claymore;

use App\Models\Currency\Currency;
use App\Models\Model;
use App\Models\User\User;
use App\Models\User\UserWeapon;

class Weapon extends Model {
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'weapon_category_id', 'name', 'has_image', 'description', 'parsed_description', 'allow_transfer',
        'parent_id', 'currency_id', 'cost',
    ];

    protected $appends = ['image_url'];

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'weapons';

    /**
     * Validation rules for creation.
     *
     * @var array
     */
    public static $createRules = [
        'weapon_category_id' => 'nullable',
        'name'               => 'required|unique:weapons|between:3,100',
        'description'        => 'nullable',
        'image'              => 'mimes:png,webp',
    ];

    /**
     * Validation rules for updating.
     *
     * @var array
     */
    public static $updateRules = [
        'weapon_category_id' => 'nullable',
        'name'               => 'required|between:3,100',
        'description'        => 'nullable',
        'image'              => 'mimes:png,webp',
    ];

    /**********************************************************************************************

        RELATIONS

    **********************************************************************************************/

    /**
     * Get the category the weapon belongs to.
     */
    public function category() {
        return $this->belongsTo(WeaponCategory::class, 'weapon_category_id');
    }

    /**
     * Get the parent of the weapon.
     */
    public function parent() {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children() {
        return $this->hasMany(self::class, 'parent_id');
    }

    public function stats() {
        return $this->hasMany(WeaponStat::class);
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
     * Scope a query to sort weapons in alphabetical order.
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
     * Scope a query to sort weapons in category order.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeSortCategory($query) {
        if (WeaponCategory::all()->count()) {
            return $query->orderBy(WeaponCategory::select('sort')->whereColumn('weapon_category_id', 'weapon_categories.id'), 'DESC');
        }

        return $query;
    }

    /**
     * Scope a query to sort weapons by newest first.
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
     * Scope a query to show only released or "released" (at least one user-owned stack has ever existed) weapons.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeReleased($query) {
        return $query->whereIn('id', UserWeapon::pluck('weapon_id')->toArray())->orWhere('is_released', 1);
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
        return 'images/data/weapons';
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
        return url('world/weapons?name='.$this->name);
    }

    /**
     * Gets the URL of the individual weapon's page, by ID.
     *
     * @return string
     */
    public function getIdUrlAttribute() {
        return url('world/weapons/'.$this->id);
    }

    /**
     * Gets the currency's asset type for asset management.
     *
     * @return string
     */
    public function getAssetTypeAttribute() {
        return 'weapons';
    }

    /**
     * Gets the admin edit URL.
     *
     * @return string
     */
    public function getAdminUrlAttribute() {
        return url('admin/weapons/edit/'.$this->id);
    }

    /**********************************************************************************************

        OTHER FUNCTIONS

    **********************************************************************************************/

    /**
     * displays the weapon's name, with stats.
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
        return UserWeapon::find($id)->imageUrl;
    }
}
