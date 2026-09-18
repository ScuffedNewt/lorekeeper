<?php

namespace App\Models\Element;

use App\Models\Model;

class Typing extends Model {
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'typing_model', 'typing_id', 'element_id',
    ];

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'typings';

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'element_ids' => 'array',
    ];

    /**********************************************************************************************

        RELATIONS

    **********************************************************************************************/

    /**
     * get the object of this type.
     */
    public function object() {
        return $this->belongsTo($this->typing_model, 'typing_id');
    }

    /**
     * get the object of this type.
     */
    public function element() {
        return $this->belongsTo(Element::class, 'element_id');
    }
    
    /**********************************************************************************************

        OTHER FUNCTIONS

    **********************************************************************************************/
    
    /**
     * Returns the element IDs assigned to an object.
     *
     * @param mixed $object
     */
    public static function elementIdsForObject($object): array {
        if (!$object) {
            return [];
        }

        $typing = self::where('typing_model', get_class($object))
            ->where('typing_id', $object->id)
            ->first();

        return $typing ? ($typing->element_ids ?? []) : [];
    }
}
