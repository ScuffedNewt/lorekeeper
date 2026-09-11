<?php

namespace App\Models\Character;

use App\Models\Model;

class CharacterFolder extends Model {
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'description', 'user_id',
    ];

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'character_folders';

    /**********************************************************************************************

        RELATIONS

    **********************************************************************************************/

    /**
     * Get the user this folder belongs to.
     */
    public function user() {
        return $this->belongsTo('App\Models\User\User', 'user_id');
    }

    /**
     * Get all characters in this folder.
     */
    public function characters() {
        return $this->hasMany('App\Models\Character\Character', 'folder_id');
    }

    /**********************************************************************************************

        ATTRIBUTES

    **********************************************************************************************/

    /**
     * returns the folder url.
     */
    public function getUrlAttribute() {
        return url('user/'.$this->user->name.'/characters/'.$this->name);
    }
}
