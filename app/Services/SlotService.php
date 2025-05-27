<?php

namespace App\Services;

use App\Models\Recipe\CraftingSlot;
use DB;

class SlotService extends Service {
    /**
     * Creates a new slot.
     *
     * @param array                 $data
     * @param \App\Models\User\User $user
     *
     * @return \App\Models\Slot\Slot|bool
     */
    public function createSlot($data, $user) {
        DB::beginTransaction();

        try {
            if (isset($data['free'])) {
                $data['currency_id'] = null;
                $data['slot_cost'] = null;
            }

            $slot = CraftingSlot::create($data);

            return $this->commitReturn($slot);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }

    /**
     * Updates an slot.
     *
     * @param \App\Models\Slot\Slot $slot
     * @param array                 $data
     * @param \App\Models\User\User $user
     *
     * @return \App\Models\Slot\Slot|bool
     */
    public function updateSlot($slot, $data, $user) {
        DB::beginTransaction();

        try {
            if (isset($data['free'])) {
                $data['currency_id'] = null;
                $data['slot_cost'] = null;
            }

            $slot->update($data);

            return $this->commitReturn($slot);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }

    /**
     * Deletes an slot.
     *
     * @param \App\Models\Slot\Slot $slot
     *
     * @return bool
     */
    public function deleteSlot($slot) {
        DB::beginTransaction();

        try {
            // Check first if the slot is currently owned or if some other site feature uses it
            if (DB::table('user_crafting_slots')->where([['slot_id', '=', $slot->id]])->exists()) {
                throw new \Exception('At least one user currently owns this slot. Please remove the slot(s) before deleting it.');
            }

            DB::table('user_crafting_slots')->where('slot_id', $slot->id)->delete();

            $slot->delete();

            return $this->commitReturn(true);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }
}
