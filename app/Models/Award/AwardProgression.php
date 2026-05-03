<?php

namespace App\Models\Award;

use App\Models\Currency\Currency;
use App\Models\Item\Item;
use App\Models\Loot\LootTable;
use App\Models\Model;
use App\Models\Raffle\Raffle;
use App\Models\User\UserCurrency;

class AwardProgression extends Model {
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'award_id', 'type', 'type_id', 'quantity',
    ];

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'award_progressions';

    /**********************************************************************************************

        ATTRIBUTES

     **********************************************************************************************/

    /**
     * Serve progression type the same way as loot
     * so as to keep the loot blades being re-used as simple as possible.
     */
    public function getRewardableTypeAttribute() {
        return $this->type;
    }

    /**
     * Serve progression type id the same way as loot
     * so as to keep the loot blades being re-used as simple as possible.
     */
    public function getRewardableIdAttribute() {
        return $this->type_id;
    }

    /**********************************************************************************************

        RELATIONS

     **********************************************************************************************/

    /**
     * Get the award the progression belongs to.
     */
    public function award() {
        return $this->belongsTo(Award::class, 'award_id');
    }

    /**
     * get the type of award progression.
     */
    public function progression() {
        switch ($this->type) {
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
            case 'Award':
                return $this->belongsTo(Award::class, 'rewardable_id');
        }

        return null;
    }

    /**********************************************************************************************

        OTHER FUNCTIONS

    **********************************************************************************************/
    /**
     * Checks if the user has the progression.
     *
     * @param mixed $user
     */
    public function isUnlocked($user) {
        switch ($this->type) {
            case 'Item':
                // get all the user items with this id and sum the quantity
                return boolval($user->items()->where('item_id', $this->type_id)->sum('count') >= $this->quantity);
                break;
            case 'Currency':
                return UserCurrency::where('user_id', $user->id)->where('currency_id', $this->type_id)->sum('quantity') >= $this->quantity;
                break;
            case 'Award':
                return boolval($user->awards()->where('award_id', $this->type_id)->sum('count') >= $this->quantity);
                break;
        }

        return false;
    }

    /**
     * returns image based on whether or not user has this progression.
     *
     * @param mixed|null $user
     * @param mixed      $isStaff
     */
    public function unlocked($user = null, $isStaff = false) {
        if ($user) {
            if ($this->isUnlocked($user)) {
                return
                $this->progression->imageUrl ?
                    '<img src="'.$this->progression->imageUrl.'" class="img-fluid" data-toggle="tooltip" title="'.$this->progression->name.' (Unlocked)">'
                    :
                    '<span class="text-success">'.$this->progression->displayName.' (Unlocked) </span>';
            } else {
                return
                $this->progression->imageUrl ?
                    '<img src="'.$this->progression->imageUrl.'" class="img-fluid" style="filter: grayscale(50%);" data-toggle="tooltip" title="'.$this->progression->name.' (Not Unlocked)">'
                    :
                    '<span class="text-danger">'.$this->progression->displayName.' (Not Unlocked) </span>';
            }
        }

        if ($isStaff) {
            return
            $this->progression->imageUrl ?
                '<img src="'.$this->progression->imageUrl.'" class="img-fluid" data-toggle="tooltip" title="'.$this->progression->name.' (Unlocked)">'
                :
                '<span class="text-success">'.$this->progression->displayName.' (Unlocked) </span>';
        }

        return
        $this->progression->imageUrl ?
            '<img src="'.$this->progression->imageUrl.'" class="img-fluid" style="filter: grayscale(50%);" data-toggle="tooltip" title="'.$this->progression->name.' (Not Unlocked)">'
            :
            '<span class="text-danger">'.$this->progression->displayName.' (Not Unlocked) </span>';
    }
}
