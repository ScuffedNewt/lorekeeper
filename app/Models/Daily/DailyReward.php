<?php

namespace App\Models\Daily;

use App\Models\Currency\Currency;
use App\Models\Item\Item;
use App\Models\Loot\LootTable;
use App\Models\Model;
use App\Models\Raffle\Raffle;

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
                return $this->belongsTo(Item::class, 'rewardable_id');
                break;
            case 'Currency':
                return $this->belongsTo(Currency::class, 'rewardable_id');
                break;
            case 'LootTable':
                return $this->belongsTo(LootTable::class, 'rewardable_id');
                break;
            case 'Raffle':
                return $this->belongsTo(Raffle::class, 'rewardable_id');
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
                return (isset($this->reward->imageUrl)) ? $this->reward->imageUrl : '/images/inventory.png';
            case 'Currency':
                return (isset($this->reward->currencyImageUrl)) ? $this->reward->currencyImageUrl : '/images/currency.png';
            case 'LootTable':
                return '/images/loot.png';
            case 'Raffle':
                return '/images/raffle.png';
        }

        return null;
    }
}
