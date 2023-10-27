<?php

namespace App\Models\Pairing;

use App\Models\Character\Character;
use App\Models\Model;
use App\Models\User\UserItem;
use Carbon\Carbon;

class Pairing extends Model {
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id', 'character_1_id', 'character_2_id', 'character_1_approved', 'character_2_approved', 'status', 'data',
    ];

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'pairings';

    /**
     * Whether the model contains timestamps to be saved and updated.
     *
     * @var string
     */
    public $timestamps = true;

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
        'user_id'        => 'required',
        'character_1_id' => 'required',
        'character_2_id' => 'required',
        'item_id'        => 'required',
        'status'         => 'required',
    ];

    /**
     * Validation rules for pairing updating.
     *
     * @var array
     */
    public static $updateRules = [
        'user_id'        => 'required',
        'character_1_id' => 'required',
        'character_2_id' => 'required',
    ];

    /**********************************************************************************************

        RELATIONS

    **********************************************************************************************/

    /**
     * Get the user who owns the pairing.
     */
    public function user() {
        return $this->belongsTo('App\Models\User\User');
    }

    /**
     * Get the character 1 associated with the pairing.
     */
    public function character_1() {
        return $this->belongsTo('App\Models\Character\Character', 'character_1_id');
    }

    /**
     * Get the character 2 associated with the pairing.
     */
    public function character_2() {
        return $this->belongsTo('App\Models\Character\Character', 'character_2_id');
    }

    /**********************************************************************************************

        SCOPES

    **********************************************************************************************/

    /**
     * Scope a query to sort features by newest first.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeSortNewest($query) {
        return $query->orderBy('id', 'DESC');
    }

    /**
     * Scope a query to sort features oldest first.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeSortOldest($query) {
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
    public function getDisplayNameAttribute() {
        return '<a href="'.$this->url.'" class="display-prompt">'.$this->name.'</a>';
    }

    /**
     * Get the data attribute as an associative array.
     *
     * @return array
     */
    public function getDataAttribute() {
        return json_decode($this->attributes['data'], true);
    }

    /**
     * Displays all the items attached to the pairing.
     *
     * @return string
     */
    public function getDisplayItemsAttribute() {
        $items = [];
        foreach ($this->data['user']['user_items'] as $id=>$q) {
            $items[] = UserItem::find($id)->item->display_name.' x'.$q;
        }

        return implode(', ', $items);
    }
}
