<?php

namespace App\Models;

use App\Models\Model;

class ObjectReward extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'object_id', 'object_type', 'rewardable_id', 'rewardable_type', 'quantity',
    ];

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'object_rewards';

    /**********************************************************************************************
    RELATIONS
     **********************************************************************************************/

    /**
     * Get the object.
     */
    public function object()
    {
        switch ($this->object_type) {
            case 'Prompt':
                return $this->belongsTo('App\Models\Prompt\Prompt', 'object_id');
                break;
        }
        return null;
    }

    /**
     * Get the reward attached to the prompt reward.
     */
    public function reward() 
    {
        switch ($this->rewardable_type)
        {
            case 'Item':
                return $this->belongsTo('App\Models\Item\Item', 'rewardable_id');
                break;
            case 'Currency':
                return $this->belongsTo('App\Models\Currency\Currency', 'rewardable_id');
                break;
            case 'LootTable':
                return $this->belongsTo('App\Models\Loot\LootTable', 'rewardable_id');
                break;
            case 'Raffle':
                return $this->belongsTo('App\Models\Raffle\Raffle', 'rewardable_id');
                break;
        }
        return null;
    }
}