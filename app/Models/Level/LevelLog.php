<?php

namespace App\Models\Level;

use App\Models\Character\Character;
use App\Models\Model;
use App\Models\User\User;

class LevelLog extends Model {
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'leveller_type', 'recipient_id', 'previous_level', 'new_level', 'created_at', 'updated_at',
    ];

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'level_log';

    /**
     * Whether the model contains timestamps to be saved and updated.
     *
     * @var string
     */
    public $timestamps = true;

    /**********************************************************************************************

        RELATIONS

    **********************************************************************************************/

    /**
     * Get the user who received the logged action.
     */
    public function recipient() {
        if ($this->recipient_type == 'User') {
            return $this->belongsTo(User::class, 'leveller_type');
        }

        return $this->belongsTo(Character::class, 'leveller_type');
    }
}
