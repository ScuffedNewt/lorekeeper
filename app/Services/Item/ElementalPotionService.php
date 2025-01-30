<?php

namespace App\Services\Item;

use App\Models\Character\Character;
use App\Models\Element\Element;
use App\Models\Element\Typing;
use App\Services\InventoryManager;
use App\Services\Service;
use App\Services\TypingManager;
use Illuminate\Support\Facades\DB;

class ElementalPotionService extends Service {
    /*
    |--------------------------------------------------------------------------
    | Elemental Potion Service
    |--------------------------------------------------------------------------
    |
    | Handles the application of elements to characters / other things.
    |
    */

    /**
     * Retrieves any data that should be used in the item tag editing form.
     *
     * @return array
     */
    public function getEditData() {
        return [
            'elements' => Element::orderBy('name')->pluck('name', 'id'),
        ];
    }

    /**
     * Processes the data attribute of the tag and returns it in the preferred format.
     *
     * @param mixed $tag
     *
     * @return mixed
     */
    public function getTagData($tag) {
        $data['element_id'] = $tag->data['element_id'] ?? null;

        return $data;
    }

    /**
     * Processes the data attribute of the tag and returns it in the preferred format.
     *
     * @param mixed $tag
     * @param array $data
     *
     * @return bool
     */
    public function updateData($tag, $data) {
        $potionData['element_id'] = $data['element_id'];

        DB::beginTransaction();

        try {
            $tag->update(['data' => json_encode($potionData)]);

            return $this->commitReturn(true);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }

    /**
     * Acts upon the item when used from the inventory.
     *
     * @param \App\Models\User\UserItem $stacks
     * @param \App\Models\User\User     $user
     * @param array                     $data
     *
     * @return bool
     */
    public function act($stacks, $user, $data) {
        DB::beginTransaction();

        try {
            $character = Character::find($data['character_id']);

            foreach ($stacks as $key=> $stack) {
                if ($stack->user_id != $user->id) {
                    throw new \Exception('This item does not belong to you.');
                }
                if ($data['quantities'][$key] > 1) {
                    throw new \Exception('You can only apply one element at a time.');
                }

                // Next, try to delete the box item. If successful, we can start distributing rewards.
                if ((new InventoryManager)->debitStack($stack->user, 'Potion Consumed', ['data' => 'Potion used on '.$character->displayName], $stack, $data['quantities'][$key])) {
                    for ($q = 0; $q < $data['quantities'][$key]; $q++) {
                        $service = new TypingManager;
                        // check if typing exists on character
                        $typing = Typing::where('typing_model', get_class($character->image))->where('typing_id', $character->image->id)->first();
                        if (!$typing) {
                            if (!$service->createTyping(get_class($character->image), $character->image->id, [$stack->item->tag($data['tag'])->data['element_id']])) {
                                foreach ($service->errors()->getMessages()['error'] as $error) {
                                    flash($error)->error();
                                }

                                throw new \Exception('Failed to create typing.');
                            }
                        } else {
                            if (!$service->editTyping($typing, array_merge($typing->element_ids, [$stack->item->tag($data['tag'])->data['element_id']]))) {
                                foreach ($service->errors()->getMessages()['error'] as $error) {
                                    flash($error)->error();
                                }

                                throw new \Exception('Failed to edit typing.');
                            }
                        }
                    }
                }
            }
            flash('You have successfully applied the element to your character, '.$character->displayName.'.')->success();

            return $this->commitReturn(true);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }
}
