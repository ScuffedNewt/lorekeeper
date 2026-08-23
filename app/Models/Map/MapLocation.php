<?php

namespace App\Models\Map;

use App\Models\Model;

class MapLocation extends Model {
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'map_id', 'description',  'cords', 'shape', 'link', 'link_type', 'is_active',
    ];

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'map_locations';

    /**********************************************************************************************

        RELATIONS

    **********************************************************************************************/

    /**
     * Get the locations that belong to the map.
     */
    public function map() {
        return $this->belongsTo('App\Models\Map\Map');
    }

    /**********************************************************************************************

        SCOPES

    **********************************************************************************************/

    /**
     * Scope a query to retrieve only active tags.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActive($query) {
        return $query->where('is_active', 1);
    }
}
