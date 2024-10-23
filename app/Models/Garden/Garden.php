<?php

namespace App\Models\Garden;

use App\Models\Model;
use App\Models\User\UserItem;

class Garden extends Model {
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'description', 'has_image', 'has_background_image', 'has_plot_image', 'is_active', 'can_perish', 'can_use_modifiers',
        'allowed_object_types',
    ];

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'gardens';

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'allowed_object_types' => 'array',
    ];


    /**********************************************************************************************

        RELATIONS

    **********************************************************************************************/

    /**
     * Get the user who owns the stack.
     */
    public function plots() {
        return $this->hasMany(GardenPlot::class, 'garden_id');
    }

    /**********************************************************************************************

        SCOPES

    **********************************************************************************************/

    /**
     * Scope a query to show only active gardens.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param mixed|null                            $user
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeVisible($query, $user = null) {
        if ($user && $user->hasPower('manage_gardens')) {
            return $query;
        }

        return $query->where('is_active', 1);
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
        return '<a href="'.$this->url.'" class="display-item">'.$this->name.'</a>';
    }

    /**
     * Gets the file directory containing the model's image.
     *
     * @return string
     */
    public function getImageDirectoryAttribute() {
        return 'images/data/gardens';
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
        return url('world/gardens?name='.$this->name);
    }

    /**
     * Gets the URL of the individual item's page, by ID.
     *
     * @return string
     */
    public function getIdUrlAttribute() {
        return url('world/gardens/'.str_replace(' ', '-', $this->name));
    }

    /**********************************************************************************************

        OTHER FUNCTIONS

    **********************************************************************************************/

    /**
     * Get the user's garden objects.
     *
     * @param User $user
     *
     * @return array
     */
    public function getUserGardenObjects($user) {
        $gardenObjects = $this->allowed_object_types ? GardenObject::whereIn('type', $this->allowed_object_types)->get() : GardenObject::all();
        $user_garden_objects = [];
        foreach ($gardenObjects as $gardenObject) {
            switch ($gardenObject->object_model) {
                case 'App\Models\Item\Item':
                    if (!isset($user_garden_objects['items'][$gardenObject->type])) {
                        $user_garden_objects['items'][$gardenObject->type] = collect();
                    }
                    $user_garden_objects['items'][$gardenObject->type]->push(
                        // merge them all into one collection
                        UserItem::where('user_id', $user->id)->where('item_id', $gardenObject->object_id)->where('count', '>', 0)->get()
                    );
                    break;

                default:
                    flash('Unknown garden object type: '.$gardenObject->object_model)->error();

                    return redirect()->to('/');
                    break;
            }
        }
        foreach ($user_garden_objects as $key => $objects) {
            foreach ($objects as $type=>$user_garden_object) {
                $user_garden_objects[$key][$type] = $user_garden_object->flatten();
            }
        }

        return $user_garden_objects;
    }
}
