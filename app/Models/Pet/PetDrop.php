<?php

namespace App\Models\Pet;

use App\Models\Model;
use App\Models\User\User;
use App\Models\User\UserPet;
use Carbon\Carbon;

class PetDrop extends Model {
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'drop_id', 'user_pet_id', 'parameters', 'drops_available', 'next_day',
    ];

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'pet_drops';

    /**
     * Dates on the model to convert to Carbon instances.
     *
     * @var array
     */
    public $dates = ['next_day'];

    /**********************************************************************************************

        RELATIONS

    **********************************************************************************************/

    /**
     * Get the associated user pet.
     */
    public function user_pet() {
        return $this->belongsTo(UserPet::class, 'user_pet_id');
    }

    /**
     * Get the category the user pet belongs to.
     */
    public function dropData() {
        return $this->belongsTo(PetDropData::class, 'drop_id');
    }

    /**********************************************************************************************

        SCOPES

    **********************************************************************************************/

    /**
     * Scope a query to only include drops that require updating.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeRequiresUpdate($query) {
        return $query->whereIn('drop_id', PetDropData::where('is_active', 1)->pluck('id')->toArray())->where('next_day', '<', Carbon::now());
    }

    /**********************************************************************************************

        ACCESSORS

    **********************************************************************************************/

    /**
     * Get the display of the group a user pet belongs to, so long as the species has more than one.
     */
    public function getGroupAttribute() {
        if (count($this->dropData->parameters) > 1) {
            return ' ('.$this->parameters.')';
        } else {
            return null;
        }
    }
}
