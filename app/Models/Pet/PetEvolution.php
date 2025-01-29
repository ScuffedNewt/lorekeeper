<?php

namespace App\Models\Pet;

use App\Models\Model;
use Illuminate\Support\Facades\File;

class PetEvolution extends Model {
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'pet_id', 'evolution_name', 'evolution_stage',
    ];

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'pet_evolutions';

    /**
     * Whether the model contains timestamps to be saved and updated.
     *
     * @var string
     */
    public $timestamps = false;

    /**********************************************************************************************

        RELATIONS

    **********************************************************************************************/

    /**
     * Get the pet associated with this pet stack.
     */
    public function pet() {
        return $this->belongsTo(Pet::class);
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
        return '<a href="'.$this->pet->idUrl.'" class="display-item">'.$this->name.' '.$this->pet->name.'</a>';
    }

    /**
     * Gets the file directory containing the model's image.
     *
     * @return string
     */
    public function getImageDirectoryAttribute() {
        return 'images/data/pets/evolutions';
    }

    /**
     * Gets the file name of the model's image.
     *
     * @return string
     */
    public function getImageFileNameAttribute() {
        return $this->pet_id.'-evolution-'.$this->id.'-image.png';
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
}
