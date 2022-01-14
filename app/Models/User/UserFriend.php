<?php

namespace App\Models\User;

use Auth;
use Config;
use Carbon\Carbon;
use App\Models\Model;

class UserFriend extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'initiator_id', 'recipient_id', 'recipient_approval', 'created_at', 'approved_at'
    ];

    /**
     * Dates on the model to convert to Carbon instances.
     *
     * @var array
     */
    protected $dates = ['created_at', 'approved_at'];

    /**********************************************************************************************

        RELATIONS

    **********************************************************************************************/

    /**
     * Get the user who created the friendship.
     */
    public function initiator()
    {
        return $this->belongsTo('App\Models\User\User', 'initiator_id');
    }

    /**
     * Get the user who is blocked.
     */
    public function recipient()
    {
        return $this->belongsTo('App\Models\User\User', 'recipient_id');
    }

    /**********************************************************************************************

        ATTRIBUTES

    **********************************************************************************************/

    /**
     * Get friend display name
     */
    public function displayName($id)
    {
        if($this->recipient_id == $id)
        {
            return '<img src="/images/avatars/'. $this->initiator->avatar .'" style="width:2em; height:2em; float:left; border-radius:50%; margin-right:25px;">' . $this->initiator->displayName;
        }
        return '<img src="/images/avatars/'. $this->recipient->avatar .'" style="width:2em; height:2em; float:left; border-radius:50%; margin-right:25px;">' . $this->recipient->displayName;
    }
}
