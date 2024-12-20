<?php

namespace App\Models\Daily;

use App\Models\Model;

class DailyTimer extends Model {
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'daily_id', 'user_id', 'rolled_at', 'step',
    ];

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'daily_timers';

    /**
     * Validation rules for creation.
     *
     * @var array
     */
    public static $createRules = [
    ];

    /**********************************************************************************************

        RELATIONS

    **********************************************************************************************/

    /**********************************************************************************************

        OTHER FUNCTIONS

    **********************************************************************************************/
}
