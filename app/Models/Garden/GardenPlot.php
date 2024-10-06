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

}
