<?php

namespace App\Models\User;

use App\Models\Model;

class UserIp extends Model {
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'ip', 'user_id',
    ];

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'user_ips';

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
     * Get the user this set of settings belongs to.
     */
    public function user() {
        return $this->belongsTo(User::class);
    }

    /**********************************************************************************************

        ATTRIBUTES

    **********************************************************************************************/

    /**
     * Gets ALL users that have used this IP.
     */
    public function getUsersAttribute() {
        return User::whereIn('id', self::where('ip', $this->ip)->pluck('user_id'))->get();
    }

    /**
     * Returns all users in an imploded, formatted string.
     *
     * @return string
     */
    public function getUsersStringAttribute() {
        $users = $this->users;
        $userList = [];
        foreach ($users as $user) {
            $userList[] = $user->displayName;
        }

        return implode(', ', $userList);
    }
}
