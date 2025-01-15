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
        'pet_category_id', 'name', 'has_image', 'description', 'parsed_description', 'allow_transfer', 'limit', 'evolution_stage', 'is_visible',
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
        return $this->hasMany(PetVariant::class, 'pet_id');
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
        return '<a href="'.$this->idUrl.'" class="display-item">'.$this->name.'</a>';
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
     * returns the variant image for the pet.
     *
     * @param mixed|null $id
     */
    public function VariantImage($id = null) {
        if (!$id) {
            return $this->imageUrl;
        }

        $userpet = UserPet::find($id);
        if (!$userpet) {
            return $this->imageUrl;
        }

        // custom image takes prescendence over all other images
        elseif ($userpet->has_image) {
            return $userpet->imageUrl;
        }
        // check if there is an evolution and variant
        elseif ($userpet->evolution_id && $userpet->variant_id) {
            return $userpet->evolution->variantImageUrl($userpet->variant_id);
        }
        // evolution > variant
        elseif ($userpet->evolution_id) {
            return $userpet->evolution->imageUrl;
        } elseif ($userpet->variant_id) {
            return $userpet->variant->imageUrl;
        }

        // default
        return $this->imageUrl;
    }

    public function VariantName($id = null) {
        if (!$id || !$this->variants()) {
            return '';
        } else {
            return $this->variants()->where('id', $id)->first()->variant_name;
        }
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
}
