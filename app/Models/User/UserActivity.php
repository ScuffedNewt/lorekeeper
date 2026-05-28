<?php

namespace App\Models\User;

use App\Models\Model;

class UserActivity extends Model {
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id', 'garden_guest_waters_today',
    ];

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'user_activities';

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        //
    ];

    /**
     * The primary key of the model.
     *
     * @var string
     */
    public $primaryKey = 'user_id';

    /**********************************************************************************************

        RELATIONS

    **********************************************************************************************/

    /**
     * Get the user this set of settings belongs to.
     */
    public function user() {
        return $this->belongsTo(User::class);
    }
}
