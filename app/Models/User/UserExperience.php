<?php

namespace App\Models\User;

use App\Models\Claymore\Experience;
use App\Models\Model;

class UserExperience extends Model {
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id', 'experience_id', 'quantity', 'data',
    ];

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'user_experience_points';

    /**********************************************************************************************

        RELATIONS

    **********************************************************************************************/

    /**
     * Get the user.
     */
    public function user() {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the experience.
     */
    public function experience() {
        return $this->belongsTo(Experience::class, 'experience_id');
    }

    /**********************************************************************************************

        ATTRIBUTES

    **********************************************************************************************/
}
