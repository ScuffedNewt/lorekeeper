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
        'typing_model', 'typing_id', 'element_ids',
    ];

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'typings';

    /**********************************************************************************************

        RELATIONS

    **********************************************************************************************/

    /**
     * get the object of this type.
     */
    public function object() {
        return $this->belongsTo($this->typing_model, 'typing_id');
    }

    /**********************************************************************************************

        OTHER FUNCTIONS

    **********************************************************************************************/

    /**
     * checks if a certain object has a typing.
     *
     * @param mixed $object
     */
    public static function hasTyping($object) {
        return self::where('typing_model', get_class($object))->where('typing_id', $object->id)->exists();
    }

    /**
     * returns a collection of element objects from the typing.
     */
    public function elements() {
        return Element::whereIn('id', json_decode($this->element_ids))->get();
    }

    /**
     * returns an imploded string of element names from the typing.
     */
    public function getElementNamesAttribute() {
        // get the displayName attribute from each element
        $names = $this->elements()->map(function ($element) {
            return $element->displayName;
        });

        return implode(', ', $names->toArray());
    }

    /**
     * displays the elements as pill badges.
     */
    public function getDisplayElementsAttribute() {
        // get the displayName attribute from each element
        $elements = $this->elements()->map(function ($element) {
            // check if first in loop
            return '<a href="'.$element->idUrl.'"><span class="badge '.($element->id == $this->elements()->first()->id ? '' : 'ml-1')
            .'" style="color: white; background-color: '.$element->colour.';">'.$element->name.'</span></a>';
        });

        return implode(' ', $elements->toArray());
    }
}
