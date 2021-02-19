<?php namespace App\Services;

use App\Services\Service;

use DB;
use Config;

use App\Models\Recipe\CraftingSlot;
use App\Models\Currency\Currency;
use App\Models\User\UserCraftingSlot;
use App\Services\CurrencyManager;

class SlotManager extends Service
{   
    /**
     * purchases a slot for a user
     *
     * @param  array                  $data
     * @param  \App\Models\User\User  $user
     * @return bool|\App\Models\Slot\Slot
     */
    public function purchaseSlot($slot, $user)
    {
        DB::beginTransaction();

        try {

            if(!$slot) throw new \Exception('Not a valid slot.');
            
            if($slot->free)
            {
                if(!$this->createSlot($user, $slot)) throw new \Exception('Error creating slot for user.');
            }
            else {

                $service = new CurrencyManager;

                $currency = Currency::find($slot->currency_id);
                if(!$currency) throw new \Exception('Could not find currency');
                $quantity = $slot->slot_cost;

                if($quantity <= 0) throw new \Exception('Quantity cannot be 0 or less.');

                if(!$service->debitCurrency($user, null, 'Bought Crafting Slot', 'Bought Crafing Slot #' . $slot->id .' for ' . $slot->slot_cost . ' ' . $currency->name, $currency, $quantity)) throw new \Exception('Not enough currency to buy this.');

                if(!$this->createSlot($user, $slot)) throw new \Exception('Error creating slot for user.');

            }

            return $this->commitReturn($slot);
        } catch(\Exception $e) {
            $this->setError('error', $e->getMessage());
        }
        return $this->rollbackReturn(false);
    }

    public function createSlot($user, $slot)
    {
        DB::beginTransaction();

        try {
            if(!$slot) throw new \Exception('Not a valid slot.');

            $madeSlot = UserCraftingSlot::create([
                'user_id' => $user->id,
                'slot_id' => $slot->id,
            ]);

            return $this->commitReturn($madeSlot);
        } catch(\Exception $e) {
            $this->setError('error', $e->getMessage());
        }
        return $this->rollbackReturn(false);
    }
}