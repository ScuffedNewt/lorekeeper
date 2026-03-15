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
        'name', 'description', 'has_image', 'has_background_image', 'has_plot_image', 'is_active', 'can_perish',
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
     * Gets the file name of the model's image.
     *
     * @return string
     */
    public function getBackgroundImageFileNameAttribute() {
        return $this->id.'-background-image.png';
    }

    /**
     * Gets the file name of the model's image.
     *
     * @return string
     */
    public function getPlotImageFileNameAttribute() {
        return $this->id.'-plot-image.png';
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
     * Gets the URL of the model's background image.
     *
     * @return string
     */
    public function getBackgroundImageUrlAttribute() {
        if (!$this->has_background_image) {
            return null;
        }

        return asset($this->imageDirectory.'/'.$this->backgroundImageFileName);
    }

    /**
     * Gets the URL of the model's background image.
     *
     * @return string
     */
    public function getPlotImageUrlAttribute() {
        if (!$this->has_plot_image) {
            return null;
        }

        return asset($this->imageDirectory.'/'.$this->plotImageFileName);
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
     * @param User       $user
     * @param mixed|null $type
     *
     * @return array
     */
    public function getUserGardenObjects($user, $type = null) {
        $types = $type ? array_keys(getGardenObjectTypes($type)) : null;
        if ($this->allowed_object_types) {
            $types = $types ? array_values(array_intersect($this->allowed_object_types, $types)) : $this->allowed_object_types;
        }
        if (empty($types)) {
            return [];
        }
        $gardenObjects = GardenObject::whereIn('type', $types)->get();
        $userGardenObjects = array_fill_keys($types, []);
        $groupedObjects = $gardenObjects->groupBy('object_model');
        foreach ($groupedObjects as $objectModel => $objects) {
            /*
             * Returns in the format:
             * (array) userGardenObjects => [
             *     objectType => [
             *         objectModel => [
             *             objectId: quantity
             *        ]
             *     ]
             * ]
             */
            switch ($objectModel) {
                case 'App\Models\Item\Item':
                    $itemIds = $objects->pluck('object_id')->unique();
                    $itemSums = UserItem::where('user_id', $user->id)
                        ->where('count', '>', 0)
                        ->whereIn('item_id', $itemIds)
                        ->get()
                        ->groupBy('item_id')
                        ->map->sum('available_quantity');

                    foreach ($objects as $gardenObject) {
                        $userGardenObjects[$gardenObject->type][$gardenObject->object_model][$gardenObject->object_id] =
                            (int) ($itemSums[$gardenObject->object_id] ?? 0);
                    }
                    break;

                    // case 'App\Models\Pet\Pet':
                    //     similar logic here
                    //     break;

                default:
                    flash('Unknown garden object type: '.$objectModel)->error();

                    return redirect()->to('gardens');
            }
        }

        return $userGardenObjects;
    }
}
