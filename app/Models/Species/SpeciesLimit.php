<?php

namespace App\Models\Species;

use App\Models\Model;

class SpeciesLimit extends Model {
    // A generic class that can be used to limit things to certain species / subtypes without having to create a new table for each type of limit.

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'species_id', 'type', 'type_id', 'is_subtype',
    ];

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'species_limits';

    /**********************************************************************************************

        RELATIONS

    **********************************************************************************************/

    /**
     * Get the limit object.
     */
    public function limit() {
        if ($this->is_subtype) {
            return $this->belongsTo(Subtype::class, 'species_id');
        }

        return $this->belongsTo(Species::class, 'species_id');
    }

    /**
     * Get the type of limit.
     */
    public function type() {
        $model = getAssetModelString(strtolower($this->type));

        return $this->belongsTo($model);
    }
}
