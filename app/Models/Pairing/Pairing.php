<?php

namespace App\Models\Pairing;

use Config;
use DB;
use Carbon\Carbon;
use App\Models\Model;
use App\Models\Character\Character;
use App\Models\User\User;

class Pairing extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id', 'item_id', 'character_1_id', 'character_2_id', 'character_1_approved', 'character_2_approved', 'status', 'data'
    ];

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'pairing';

    /**
     * Dates on the model to convert to Carbon instances.
     *
     * @var array
     */
    public $dates = ['created_at'];

    /**
     * Validation rules for pairing creation.
     *
     * @var array
     */
    public static $createRules = [
        'user_id' => 'required',
        'character_1_id' => 'required',
        'character_2_id' => 'required',
        'item_id' => 'required',
        'status' => 'required'
    ];

    /**
     * Validation rules for pairing updating.
     *
     * @var array
     */
    public static $updateRules = [
        'user_id' => 'required',
        'character_1_id' => 'required',
        'character_2_id' => 'required',
    ];

    /**********************************************************************************************

        RELATIONS

    **********************************************************************************************/
    /**
     * Get the character 1 associated with the pairing.
     */
    public function character_1()
    {
        return $this->belongsTo('App\Models\Character\Character', 'character_1_id');
    }

    /**
     * Get the character 2 associated with the pairing.
     */
    public function character_2()
    {
        return $this->belongsTo('App\Models\Character\Character', 'character_2_id');
    }


    /**********************************************************************************************

        SCOPES

    **********************************************************************************************/

    /**
     * Scope a query to sort features by newest first.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeSortNewest($query)
    {
        return $query->orderBy('id', 'DESC');
    }

    /**
     * Scope a query to sort features oldest first.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeSortOldest($query)
    {
        return $query->orderBy('id');
    }

    /**********************************************************************************************

        ACCESSORS

    **********************************************************************************************/

    /**
     * Displays the model's name, linked to its encyclopedia page.
     *
     * @return string
     */
    public function getDisplayNameAttribute()
    {
        return '<a href="'.$this->url.'" class="display-prompt">'.$this->name.'</a>';
    }

    /**
     * Gets the URL of the model's encyclopedia page.
     *
     * @return string
     */
    public function getUrlAttribute()
    {
        return url('pairings/pairings?name='.$this->name);
    }

    /**
     * Gets the pairings's asset type for asset management.
     *
     * @return string
     */
    public function getAssetTypeAttribute()
    {
        return 'pairings';
    }

    /**
     * Get the data attribute as an associative array.
     *
     * @return array
     */
    public function getDataAttribute()
    {
        return json_decode($this->attributes['data'], true);
    }
}
