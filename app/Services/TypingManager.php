<?php

namespace App\Services;

use App\Models\Element\Typing;
use Auth;
use DB;
use Log;

class TypingManager extends Service {
    /*
    |--------------------------------------------------------------------------
    | Typng Manager
    |--------------------------------------------------------------------------
    |
    | Handles the creation and editing of elements on objects
    |
    */

    /**********************************************************************************************

        TYPINGS

    **********************************************************************************************/

    /**
     * Creates a new typing for an object.
     *
     * @param mixed      $typing_model
     * @param mixed      $typing_id
     * @param mixed|null $element_ids
     * @param mixed      $log
     */
    public function createTyping($typing_model, $typing_id, $element_ids = null, $log = true) {
        DB::beginTransaction();

        try {
            if (!$element_ids) {
                throw new \Exception('No elements provided.');
            }
            // check that there is not more than two element ids
            if (count($element_ids) > 2) {
                throw new \Exception('Too many elements provided.');
            }
            // check that there is not duplicate element ids
            if (count($element_ids) != count(array_unique($element_ids))) {
                throw new \Exception('Duplicate elements provided.');
            }
            // check that a typing with this model and id doesn't already exist
            if (Typing::where('typing_model', $typing_model)->where('typing_id', $typing_id)->exists()) {
                throw new \Exception('A typing with this model and id already exists.');
            }

            // create the typing
            $typing = Typing::create([
                'typing_model' => $typing_model,
                'typing_id'    => $typing_id,
                'element_ids'  => $element_ids,
            ]);

            // log the action
            if ($log && !$this->logAdminAction(Auth::user(), 'Created Typing', 'Created '.$typing->object->displayName.' typing')) {
                throw new \Exception('Failed to log admin action.');
            }

            return $this->commitReturn($typing);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }

    /**
     * edits an existing typing on a model.
     *
     * @param mixed      $typing
     * @param mixed|null $element_ids
     * @param mixed      $log
     */
    public function editTyping($typing, $element_ids = null, $log = true) {
        DB::beginTransaction();

        try {
            if (!$element_ids) {
                throw new \Exception('No elements provided.');
            }
            // check that there is not more than two element ids
            if (count($element_ids) > 2) {
                throw new \Exception('Too many elements provided.');
            }
            // check that there is not duplicate element ids
            if (count($element_ids) != count(array_unique($element_ids))) {
                throw new \Exception('Duplicate elements provided.');
            }
            // check that a typing with this model and id doesn't already exist
            if (Typing::where('typing_model', $typing->typing_model)->where('typing_id', $typing->typing_id)->where('id', '!=', $typing->id)->exists()) {
                throw new \Exception('A typing with this model and id already exists.');
            }

            // create the typing
            $typing->update([
                'element_ids'  => $element_ids,
            ]);

            // log the action
            if ($log && !$this->logAdminAction(Auth::user(), 'Edited Typing', 'Edited '.$typing->object->displayName.' typing')) {
                throw new \Exception('Failed to log admin action.');
            }

            return $this->commitReturn($typing);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }

    /**
     * deletes a typing.
     *
     * @param mixed $typing
     */
    public function deleteTyping($typing) {
        DB::beginTransaction();

        try {
            // delete the typing
            $typing->delete();

            // log the action
            if (!$this->logAdminAction(Auth::user(), 'Deleted Typing', 'Deleted '.$typing->object->displayName.' typing')) {
                throw new \Exception('Failed to log admin action.');
            }

            return $this->commitReturn($typing);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }

    /**
     * Credits an element, for usage with the asset helper vs widget.
     *
     * @param mixed      $element
     * @param mixed      $recipient
     * @param mixed|null $sender
     * @param mixed|null $origin
     */
    public function creditTyping($recipient, $element, $sender = null, $origin = null) {
        DB::beginTransaction();

        try {
            $model = get_class($recipient->image);
            $id = $recipient->image->id;

            // log the action
            if (!$this->logAdminAction(
                Auth::user(),
                'Credited Typing',
                'Credited '.$element->displayName.' to '.$recipient->displayName.''.
                ($sender ? ' from '.$sender->displayName : '').
                ($origin ? ' ('.$origin.')' : '')
            )) {
                throw new \Exception('Failed to log admin action.');
            }

            $typing = Typing::where('typing_model', $model)->where('typing_id', $id)->first();

            if ($typing) {
                if (!$this->editTyping($typing, [$element->id], false)) {
                    throw new \Exception('Failed to edit typing.');
                }
            } else {
                if (!$this->createTyping($model, $id, [$element->id], false)) {
                    throw new \Exception('Failed to create typing.');
                }
            }

            return $this->commitReturn(true);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }
}
