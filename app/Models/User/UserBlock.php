<?php

namespace App\Models\User;

use Auth;
use Config;
use Carbon\Carbon;
use App\Models\Model;

class UserBlock extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id', 'blocked_id'
    ];

    /**
     * Dates on the model to convert to Carbon instances.
     *
     * @var array
     */
    protected $dates = ['created_at', 'accepted_at'];

    /**********************************************************************************************

        RELATIONS

    **********************************************************************************************/

    /**
     * Get the user who created the block.
     */
    public function user()
    {
        return $this->belongsTo('App\Models\User\User');
    }

    /**
     * Get the user who is blocked.
     */
    public function blocked()
    {
        return $this->belongsTo('App\Models\User\User', 'blocked_id');
    }
}
