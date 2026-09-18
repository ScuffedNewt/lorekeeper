<?php

namespace App\Services;

use App\Models\Character\CharacterImage;
use App\Models\Element\Typing;
use App\Traits\Typeable;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

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
            $object = $typing_model::find($typing_id);
            if(!$object) {
                throw new \Exception('Object does not exist.');
            }
            if(!in_array(Typeable::class, class_uses_recursive($object))) {
                throw new \Exception('Object can not have elements.');
            }
            // check that there is not more than two element ids
            if (!empty($element_ids) && count($element_ids) > 2) {
                throw new \Exception('Too many elements provided.');
            }

            // get old typings for logging
            if($object->typings) {
                $oldData = $object->typings->pluck('element_id')->toArray();
            } else {
                $oldData = [];
            }

            // create the typing
            $object->typings()->delete();
            $newData = collect();
            if(!empty($element_ids)) {
                // check that there is not duplicate element ids
                $element_ids = array_unique($element_ids);
                foreach($element_ids as $id) {
                    $typing = Typing::create([
                        'typing_model' => $typing_model,
                        'typing_id'    => $typing_id,
                        'element_id'  => $id,
                    ]);
                    $newData->push($typing->element);
                }
            }

            // get new typings for logging
            if($newData->isEmpty()) {
                $log = 'Typings Deleted';
            } else {
                $log = implode(', ', $newData->pluck('displayName')->toArray());
            }

            // log the action
            if ($log && !$this->logAdminAction(Auth::user(), 'Created Typing', 'Created '.$object->displayName.' '.$log)) {
                throw new \Exception('Failed to log admin action.');
            }

            if(get_class($object) == CharacterImage::class) {
                $characterManager = new CharacterManager;
                if(!$characterManager->createLog(Auth::user()->id, 
                    null, 
                    null, null, 
                    $object->character->id, 
                    'Typing Edited', 
                    $log, 
                    'character',
                    true,
                    $oldData,
                    $newData)) {
                    throw new \Exception('Failed to create log.');
                }
            }

            return $this->commitReturn($object);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }

    /**
     * deletes a object's typings.
     *
     * @param mixed $typing
     */
    public function deleteTyping($object) {
        DB::beginTransaction();

        try {
            // delete the typing
            $object->typings()->delete();

            // log the action
            if (!$this->logAdminAction(Auth::user(), 'Deleted Typing', 'Deleted '.$object->displayName.' typings')) {
                throw new \Exception('Failed to log admin action.');
            }

            return $this->commitReturn(true);
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
            $image = $recipient->image;
            $id = $recipient->image->id;

            if($image->typings->where('element_id', $element->id)->count()) {
                throw new \Exception('Element already exists.');
            }

            $oldData = $image->typings->pluck('element_id')->toArray();

            if (!$this->createTyping(get_class($image), $id, array_merge($image->typings->pluck('element_id')->toArray(), [$element->id]), false)) {
                throw new \Exception('Failed to add typing.');
            }

            $characterManager = new CharacterManager;
            if(!$characterManager->createLog(Auth::user()->id, 
                    null, 
                    null, null, 
                    $recipient->id, 
                    'Typing Credited', 
                    $element->displayName . ' typing added', 
                    'character',
                    true,
                    $oldData,
                    $oldData + [$element->id])) {
                    throw new \Exception('Failed to create log.');
                }

            return $this->commitReturn(true);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }
}
