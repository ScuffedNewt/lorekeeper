<?php

namespace App\Models\Limit;

use App\Models\Model;

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
