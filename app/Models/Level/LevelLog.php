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
        'sender_id', 'sender_type', 'recipient_id', 'leveller_type',
        'previous_level', 'new_level', 'log', 'log_type', 'data',
        'created_at', 'updated_at',
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
    public function sender() {
        if ($this->sender_type == 'User') {
            return $this->belongsTo(User::class, 'sender_id');
        }

        return $this->belongsTo(Character::class, 'sender_id');
    }

    /**
     * Get the user who received the logged action.
     */
    public function recipient() {
        if ($this->leveller_type == 'User') {
            return $this->belongsTo(User::class, 'recipient_id');
        }

        return $this->belongsTo(Character::class, 'recipient_id');
    }

    /**
     * Get the previous level.
     */
    public function previousLevel() {
        return $this->belongsTo(Level::class, 'previous_level');
    }

    /**
     * Get the new level.
     */
    public function newLevel() {
        return $this->belongsTo(Level::class, 'new_level');
    }
}
