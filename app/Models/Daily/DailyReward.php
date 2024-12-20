<?php

namespace App\Models\Daily;

use App\Models\Model;

class DailyReward extends Model {
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'daily_id', 'rewardable_type', 'rewardable_id', 'quantity', 'step',
    ];

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'daily_rewards';

    /**
     * Validation rules for creation.
     *
     * @var array
     */
    public static $createRules = [
        'rewardable_type' => 'required',
        'rewardable_id'   => 'required',
        'quantity'        => 'required|integer|min:1',
    ];

    /**
     * Validation rules for updating.
     *
     * @var array
     */
    public static $updateRules = [
        'rewardable_type' => 'required',
        'rewardable_id'   => 'required',
        'quantity'        => 'required|integer|min:1',
    ];

    /**********************************************************************************************

        RELATIONS

    **********************************************************************************************/

    /**
     * Get the reward attached to the daily reward.
     */
    public function reward() {
        switch ($this->rewardable_type) {
            case 'Item':
                return $this->belongsTo('App\Models\Item\Item', 'rewardable_id');
                break;
            case 'Currency':
                return $this->belongsTo('App\Models\Currency\Currency', 'rewardable_id');
                //uncomment if you use awards, may still have to edit the loot select blade files
                /**case 'Award':
                    return $this->belongsTo('App\Models\Award\Award', 'rewardable_id');
                    break;**/
            case 'LootTable':
                return $this->belongsTo('App\Models\Loot\LootTable', 'rewardable_id');
                break;
                //uncomment if you use pets, may still have to edit the loot select blade files
                /**case 'Pet':
                    return $this->belongsTo('App\Models\Pet\Pet', 'rewardable_id');**/
                break;
            case 'Raffle':
                return $this->belongsTo('App\Models\Raffle\Raffle', 'rewardable_id');
                break;
        }

        return null;
    }

    /*
     * Gets the display image for the reward.
     */
    public function getRewardImageAttribute() {
        switch ($this->rewardable_type) {
            case 'Item':
                return (isset($this->reward()->first()->imageUrl)) ? $this->reward()->first()->imageUrl : '/images/inventory.png';
                break;
            case 'Currency':
                return (isset($this->reward()->first()->currencyImageUrl)) ? $this->reward()->first()->currencyImageUrl : '/images/currency.png';
                //uncomment if you use awards, may still have to edit the loot select blade files
                /**case 'Award':
                    return $this->belongsTo('App\Models\Award\Award', 'rewardable_id');
                    break;**/
            case 'LootTable':
                return '/images/loot.png';
                break;
                //uncomment if you use pets, may still have to edit the loot select blade files
                /**case 'Pet':
                    return $this->belongsTo('App\Models\Pet\Pet', 'rewardable_id');**/
                break;
            case 'Raffle':
                return '/images/raffle.png';
                break;
        }

        return null;
    }
}
