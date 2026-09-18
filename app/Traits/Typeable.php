<?php

namespace App\Traits;

use App\Models\Element\Typing;

/**
 * Add this trait to any model that you want to have limits.
 */
trait Typeable {
    public function typings() {
        return $this->morphMany(Typing::class, 'typeable', 'typing_model', 'typing_id');
    }

    public function getHasTypingAttribute() {
        return $this->typings->count() !== 0;
    }

    /**
     * returns an imploded string of element names from the typing.
     */
    public function getElementNamesAttribute() {
        return implode(', ', $this->typings->pluck('element')->pluck('displayName')->toArray());
    }

    /**
     * displays the elements as pill badges.
     */
    public function getDisplayElementsAttribute() {
        return implode(' ', $this->typings->pluck('element')->pluck('displayNameBadge')->toArray());
    }

}