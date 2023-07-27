<?php

namespace App\Models\Shop;

use App\Models\Model;

class ShopStock extends Model
{

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'shop_id', 'item_id', 'currency_id', 'cost', 'use_user_bank', 'use_character_bank', 'is_limited_stock', 'quantity', 'sort', 'purchase_limit', 'purchase_limit_timeframe', 'is_fto', 'stock_type', 'is_visible',
        'restock', 'restock_quantity', 'restock_interval', 'range', 'disallow_transfer','is_timed_stock', 'start_at', 'end_at'
    ];

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'shop_stock';

    /**
     * Validation rules for creation.
     *
     * @var array
     */
    public static $createRules = [
        'purchase_limit_timeframe' => 'in:lifetime,yearly,monthly,weekly,daily',
    ];

    /**********************************************************************************************

        RELATIONS

    **********************************************************************************************/

    /**
     * Get the item being stocked.
     */
    public function item() 
    {
        $model = getAssetModelString(strtolower($this->stock_type));
        return $this->belongsTo($model);
    }

    /**
     * Get the shop that holds this item.
     */
    public function shop()
    {
        return $this->belongsTo('App\Models\Shop\Shop');
    }

    /**
     * Get the currency the item must be purchased with.
     */
    public function currency()
    {
        return $this->belongsTo('App\Models\Currency\Currency');
    }


    /*
     * Gets the current date associated to the current stocks purchase limit timeframe
     */
    public function getPurchaseLimitDateAttribute() {
        switch($this->purchase_limit_timeframe) {
            case "yearly":
                $date = strtotime('January 1st');
                break;
            case "monthly":
                $date = strtotime('midnight first day of this month');
                break;
            case "weekly":
                $date = strtotime('last sunday');
                break;
            case "daily":
                $date = strtotime('midnight');
                break;
            default:
                $date = null;
        }

        return $date;
    }

    /**********************************************************************************************

        OTHER FUNCTIONS

    **********************************************************************************************/

    /**
     * Scopes active stock
     */
    public function scopeActive($query)
    {
        return $query->where('is_visible', 1);
    }

    /**
     * Makes the cost an integer for display
     */
    public function getDisplayCostAttribute()
    {
        return (int)$this->cost;
    }
}
