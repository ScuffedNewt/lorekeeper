<?php

namespace App\Models\Character;

use App\Models\Award\Award;
use App\Models\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CharacterAward extends Model {
    use SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'data', 'award_id', 'character_id',
    ];

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'character_awards';

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'data' => 'array',
    ];

    /**
     * Whether the model contains timestamps to be saved and updated.
     *
     * @var string
     */
    public $timestamps = true;

    /**********************************************************************************************

        RELATIONS

    **********************************************************************************************/

    /**
     * Get the character who owns the stack.
     */
    public function character() {
        return $this->belongsTo(Character::class);
    }

    /**
     * Get the item associated with this item stack.
     */
    public function award() {
        return $this->belongsTo(Award::class);
    }

    /**********************************************************************************************

        ACCESSORS

    **********************************************************************************************/

    /**
     * Checks if the stack is transferrable.
     *
     * @return array
     */
    public function getIsTransferrableAttribute() {
        if (!isset($this->data['disallow_transfer']) && $this->award->allow_transfer) {
            return true;
        }

        return false;
    }

    /**
     * Gets the available quantity of the stack.
     *
     * @return int
     */
    public function getAvailableQuantityAttribute() {
        return $this->count;
    }

    /**
     * Gets the stack's asset type for asset management.
     *
     * @return string
     */
    public function getAssetTypeAttribute() {
        return 'character_awards';
    }
}
