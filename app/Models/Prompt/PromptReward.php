<?php

namespace App\Models\Prompt;

use App\Models\Currency\Currency;
use App\Models\Item\Item;
use App\Models\Loot\Loot;
use App\Models\Loot\LootTable;
use App\Models\Model;
use App\Models\Pet\Pet;
use App\Models\Raffle\Raffle;
use App\Models\Stat\Stat;

class PromptReward extends Model {
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'prompt_id', 'rewardable_type', 'rewardable_id', 'quantity',
    ];

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'prompt_rewards';
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
     * Get the reward attached to the prompt reward.
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
            case 'Pet':
                return $this->belongsTo(Pet::class, 'rewardable_id');
                break;
            case 'Raffle':
                return $this->belongsTo(Raffle::class, 'rewardable_id');
                break;
            case 'Points':
                return $this->belongsTo(Stat::class, 'rewardable_id');
                break;
            case 'Exp':
                // Laravel requires a relationship instance to be returned (cannot return null), so returning one that doesn't exist here.
                return $this->belongsTo(Loot::class, 'rewardable_id', 'loot_table_id')->whereNull('loot_table_id');
                break;
        }

        return null;
    }
}
