<?php

namespace App\Models\Shop;

use App\Models\Model;

class ShopLimit extends Model {
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'shop_id', 'item_id',
    ];

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'shop_limits';

    /**********************************************************************************************

        RELATIONS

    **********************************************************************************************/

    public function item() {
        return $this->belongsTo('App\Models\Item\Item', 'item_id');
    }
}
