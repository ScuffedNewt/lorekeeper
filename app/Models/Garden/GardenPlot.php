<?php

namespace App\Models\Garden;

use App\Models\Model;

class GardenPlot extends Model {
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'garden_id', 'data', 'has_image',
    ];

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'garden_plots';

    /**********************************************************************************************

        RELATIONS

    **********************************************************************************************/

    /**
     * Get the garden that owns the plot.
     */
    public function garden() {
        return $this->belongsTo(Garden::class, 'garden_id');
    }

    /**********************************************************************************************

        ATTRIBUTES

    **********************************************************************************************/

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
     * Returns a name for unlocking purposes.
     */
    public function getnameAttribute() {
        return 'Garden plot';
    }
}
