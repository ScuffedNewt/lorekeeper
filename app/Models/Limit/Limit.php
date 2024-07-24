<?php

namespace App\Models\Limit;

use App\Models\Model;
use App\Models\Prompt\Prompt;
use App\Models\Item\Item;
use App\Models\Currency\Currency;
use App\Models\Limit\DynamicLimit;

class Limit extends Model {

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'object_model', 'object_id', 'limit_type', 'limit_id', 'quantity', 'debit',
    ];

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'limits';

    /**********************************************************************************************

        RELATIONS

    **********************************************************************************************/

    /**
     * get the object of this type.
     */
    public function object() {
        return $this->belongsTo($this->object_model, 'object_id');
    }

    /**
     * gets the limit of this ... limit.
     */
    public function limit() {
        switch ($this->limit_type) {
            case 'prompt':
                return $this->belongsTo(Prompt::class, 'limit_id');
            case 'item':
                return $this->belongsTo(Item::class, 'limit_id');
            case 'currency':
                return $this->belongsTo(Currency::class, 'limit_id');
            case 'dynamic':
                return $this->belongsTo(DynamicLimit::class, 'limit_id');
        }
    }

    /**********************************************************************************************

        OTHER FUNCTIONS

    **********************************************************************************************/

    /**
     * checks if a certain object has any limits.
     *
     * @param mixed $object
     */
    public static function hasLimits($object) {
        return self::where('object_model', get_class($object))->where('object_id', $object->id)->exists();
    }

    /**
     * get the limits of a certain object.
     *
     * @param mixed $object
     */
    public static function getLimits($object) {
        return self::where('object_model', get_class($object))->where('object_id', $object->id)->get();
    }
}