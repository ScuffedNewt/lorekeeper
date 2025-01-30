<?php

namespace App\Services\Item;

use App\Models\Character\Character;
use App\Models\Stat\Stat;
use App\Services\InventoryManager;
use App\Services\Service;
use App\Services\Stat\StatManager;
use Illuminate\Support\Facades\DB;

class PotionService extends Service {
    /*
    |--------------------------------------------------------------------------
    | Potion Service
    |--------------------------------------------------------------------------
    |
    | Handles the editing and usage of potion type items.
    |
    */

    /**
     * Retrieves any data that should be used in the item tag editing form.
     *
     * @return array
     */
    public function getEditData() {
        return [
            'stats' => Stat::orderBy('name')->pluck('name', 'id')->toArray(),
        ];
    }

    /**
     * Processes the data attribute of the tag and returns it in the preferred format for edits.
     *
     * @param string $tag
     *
     * @return mixed
     */
    public function getTagData($tag) {
        $potionData['stat_id'] = $tag->data['stat_id'] ?? null;
        $potionData['type'] = $tag->data['type'] ?? null;
        $potionData['value'] = $tag->data['value'] ?? 0;

        return $potionData;
    }

    /**
     * Processes the data attribute of the tag and returns it in the preferred format for DB storage.
     *
     * @param string $tag
     * @param array  $data
     *
     * @return bool
     */
    public function updateData($tag, $data) {
        $potionData['stat_id'] = $data['stat_id'] ?? null;
        $potionData['type'] = $data['type'] ?? null;
        $potionData['value'] = $data['value'] ?? 0;

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
            if (!$data['character_potion_id']) {
                throw new \Exception('No character selected.');
            }

            foreach ($stacks as $key=> $stack) {
                // We don't want to let anyone who isn't the owner of the slot to use it,
                // so do some validation...
                if ($stack->user_id != $user->id) {
                    throw new \Exception('This item does not belong to you.');
                }

                // Next, try to delete the tag item. If successful, we can start applying potion effects.
                if ((new InventoryManager)->debitStack($stack->user, 'Potion Used', ['data' => ''], $stack, $data['quantities'][$key])) {
                    for ($q = 0; $q < $data['quantities'][$key]; $q++) {
                        // map to map subtract, add etc to ops
                        $operators = [
                            'add'      => '+',
                            'subtract' => '-',
                            'multiply' => '*',
                        ];
                        // Get the stat we're working with.
                        $stat = Stat::find($stack->item->tag($data['tag'])->getData()['stat_id']);
                        // get the character
                        $character = Character::find($data['character_potion_id']);
                        $characterStat = $character->stats()->where('stat_id', $stat->id)->first();

                        // If the stat doesn't exist, error
                        if (!$characterStat) {
                            throw new \Exception('Character does not have the required stat.');
                        }

                        $count = $characterStat->current_count ?? $characterStat->count;
                        $quantity = eval('return '.$count.$operators[$stack->item->tag($data['tag'])->getData()['type']].$stack->item->tag($data['tag'])->getData()['value'].';');

                        $service = new StatManager;
                        if (!$service->editCharacterStatCurrentCount(
                            $characterStat,
                            $character,
                            $quantity,
                            'Potion Used',
                            $stack->item->name.' used on '.$character->name
                        )) {
                            foreach ($service->errors()->getMessages()['error'] as $error) {
                                flash($error)->error();
                            }
                            throw new \Exception('Error updating stat.');
                        }
                    }
                }
            }

            return $this->commitReturn(true);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }
}
