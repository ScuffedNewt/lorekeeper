<?php

namespace App\Services;

use App\Models\Limit\Limit;
use App\Models\Submission\Submission;
use Auth;

class LimitManager extends Service {
    /*
    |--------------------------------------------------------------------------
    | Limit Manager
    |--------------------------------------------------------------------------
    |
    | Handles the checking of limits on objects
    |
    */

    /**********************************************************************************************

        LIMITS

    **********************************************************************************************/

    /**
     * checks all limits on an object.
     *
     * @param mixed $object
     */
    public function checkLimits($object) {
        try {
            $user = Auth::user();

            $limits = Limit::hasLimits($object) ? Limit::getLimits($object) : [];
            if (!count($limits)) {
                return true;
            }

            $plucked_stacks = [];
            foreach ($limits as $limit) {
                switch ($limit->limit_type) {
                    case 'prompt':
                        // check at least quantity of prompts has been approved
                        if (Submission::where('user_id', $user->id)->where('status', 'Approved')->where('prompt_id', $limit->limit_id)->count() < $limit->quantity) {
                            throw new \Exception('You have not completed the prompt '.$limit->object->name.' enough times to complete this action.');
                        }
                        break;
                    case 'item':
                        if (!$user->items()->where('item_id', $limit->limit_id)->sum('count') >= $limit->quantity) {
                            throw new \Exception('You do not have enough of the item '.$limit->object->name.' to complete this action.');
                        }

                        if ($limit->debit) {
                            $stacks = $user->items()->where('item_id', $limit->limit_id)->orderBy('count', 'desc')->get();


                            $count = $limit->quantity;
                            while ($count > 0) {
                                $stack = $stacks->pop();
                                $quantity = $stack->count >= $count ? $count : $stack->count;
                                if (!$service->debitStack($user, 'Limit Checking', ['data' => 'Used in ' . $object->name . '.'], $stack, $quantity)) {
                                    throw new \Exception('Items could not be removed.');
                                }
                                $count -= $quantity;
                            }
                        }
                        break;
                    case 'currency':
                        if ($user->currency($limit->limit_id) < $limit->quantity) {
                            throw new \Exception('You do not have enough '.$limit->object->name.' to complete this action.');
                        }
                        break;
                }
            }

            if (count($plucked_stacks)) {
                $inventoryManager = new InventoryManager;
                $type = 'Limit Debit';
                $data = [
                    'data' => 'Used in '.$limit->object->displayName ?? $limit->object->name.'\'s limit checks',
                ];

                foreach ($plucked_stacks as $id=>$quantity) {
                    $stack = UserItem::find($id);
                    if (!$inventoryManager->debitStack($user, $type, $data, $stack, $quantity)) {
                        throw new \Exception('Items could not be removed.');
                    }
                }

            }

            return true;
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }
    }
}
