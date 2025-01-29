<?php

namespace App\Models\Pet;

use App\Models\Model;
use App\Models\User\UserPet;
use Illuminate\Support\Facades\DB;

class Pet extends Model {
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'pet_category_id', 'name', 'has_image', 'description', 'parsed_description', 'allow_transfer', 'limit',
        'parent_id', 'is_visible',
    ];

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'pets';

    /**
     * Validation rules for creation.
     *
     * @var array
     */
    public static $createRules = [
        'pet_category_id' => 'nullable',
        'name'            => 'required|unique:pets|between:3,25',
        'description'     => 'nullable',
        'image'           => 'mimes:png',
    ];

    /**
     * Validation rules for updating.
     *
     * @var array
     */
    public static $updateRules = [
        'pet_category_id' => 'nullable',
        'name'            => 'required|between:3,25',
        'description'     => 'nullable',
        'image'           => 'mimes:png',
    ];

    /**********************************************************************************************

        RELATIONS

    **********************************************************************************************/

    /**
     * Get the category the pet belongs to.
     */
    public function category() {
        return $this->belongsTo(PetCategory::class, 'pet_category_id');
    }

    /**
     * get all the pet variants.
     */
    public function variants() {
        return $this->hasMany(Pet::class, 'parent_id');
    }

    /**
     * Get the parent pet of this variant.
     */
    public function parent() {
        return $this->belongsTo(Pet::class, 'parent_id');
    }

    /**
     * get the pets evolutions.
     */
    public function evolutions() {
        return $this->hasMany(PetEvolution::class, 'pet_id');
    }

    /**
     * Get the drop data associated with this species.
     */
    public function dropData() {
        return $this->hasOne(PetDropData::class);
    }

    /**********************************************************************************************

        SCOPES

    **********************************************************************************************/

    /**
     * Scope a query to show only visible pets.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param mixed|null                            $user
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeVisible($query, $user = null) {
        if ($user && $user->hasPower('edit_data')) {
            return $query;
        }

        return $query->where('is_visible', 1);
    }

    /**
     * Scope a query to sort pets in alphabetical order.
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
     * Scope a query to sort pets in category order.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeSortCategory($query) {
        $ids = PetCategory::orderBy('sort', 'DESC')->pluck('id')->toArray();

        return count($ids) ? $query->orderBy(DB::raw('FIELD(pet_category_id, '.implode(',', $ids).')')) : $query;
    }

    /**
     * Scope a query to sort pets by newest first.
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

    /**********************************************************************************************

        ACCESSORS

    **********************************************************************************************/

    /**
     * Displays the model's name, linked to its encyclopedia page.
     *
     * @return string
     */
    public function getDisplayNameAttribute() {
        if ($this->parent_id) {
            return '<a href="'.$this->idUrl.'" class="display-item">'.$this->name.' - Variant of '.$this->parent->name.'</a>';
        }
        return '<a href="'.$this->idUrl.'" class="display-item">'.$this->name.'</a>';
    }

    /**
     * Gets the pet's name and, if it is a variant, the parent's name.
     *
     * @return string
     */
    public function getFullNameAttribute() {
        if ($this->parent_id) {
            return $this->name . ' (' . $this->parent->name . ' Variant)';
        }

        return $this->name;
    }

    /**
     * Gets the file directory containing the model's image.
     *
     * @return string
     */
    public function getImageDirectoryAttribute() {
        return 'images/data/pets';
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
     * Gets the URL of the model's encyclopedia entry in the index.
     *
     * @return string
     */
    public function getUrlAttribute() {
        return url('world/pets?name='.$this->name);
    }

    /**
     * Gets the URL of the model's encyclopedia page.
     *
     * @return string
     */
    public function getIdUrlAttribute() {
        return url('world/pets/'.$this->id);
    }

    /**
     * Gets the currency's asset type for asset management.
     *
     * @return string
     */
    public function getAssetTypeAttribute() {
        return 'pets';
    }

    /**
     * Gets the admin edit URL.
     *
     * @return string
     */
    public function getAdminUrlAttribute() {
        return url('admin/data/pets/edit/'.$this->id);
    }

    /**
     * Gets the power required to edit this model.
     *
     * @return string
     */
    public function getAdminPowerAttribute() {
        return 'edit_data';
    }

    /**
     * returns the image for the user pet.
     *
     * @param mixed|null $id
     */
    public function image($id = null) {
        if (!$id) {
            return $this->imageUrl;
        }

        $userpet = UserPet::find($id);
        if (!$userpet) {
            return $this->imageUrl;
        }

        // custom image takes prescendence over all other images
        if ($userpet->has_image) {
            return $userpet->imageUrl;
        }
        // check if there is an evolution and variant
        elseif ($userpet->evolution_id && $this->parent_id) {
            return $userpet->evolution->imageUrl($userpet->pet);
        }
        // evolution > variant
        elseif ($userpet->evolution_id) {
            return $userpet->evolution->imageUrl;
        }

        // default
        return $this->imageUrl;
    }

    /**
     * Gets whether or not the pet has drops.
     *
     * @return string
     */
    public function getHasDropsAttribute() {
        if ($this->dropData) {
            return 1;
        } else {
            return 0;
        }
    }

    /**
     * Returns if this pet is a variant.
     */
    public function getIsVariantAttribute() {
        return $this->parent_id ? true : false;
    }
}
