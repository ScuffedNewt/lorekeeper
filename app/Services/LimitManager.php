<?php

namespace App\Services;

use App\Models\Limit\Limit;
use App\Models\Prompt\Prompt;
use App\Models\Submission\Submission;
use Auth;
use DB;
use Log;

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
     * @param mixed     $object
     */
    public function checkLimits($object) {
        try {

            $user = Auth::user();

            $limits = Limit::hasLimits($object) ? Limit::getLimits($object) : [];
            if (!count($limits)) {
                return true;
            }

            foreach ($limits as $limit) {
                switch ($limit->limit_type) {
                    case 'prompt':
                        // check at least quantity of prompts has been approved
                        if (Submission::where('user_id', $user->id)->where('status', 'Approved')->where('prompt_id', $limit->limit_id)->count() < $limit->quantity) {
                            throw new \Exception('You have not completed the prompt ' . $limit->object->name . ' enough times to complete this action.');
                        }
                        break;
                    case 'item':
                        if (!$user->items()->where('item_id', $limit->limit_id)->sum('count') >= $limit->quantity) {
                            throw new \Exception('You do not have enough of the item ' . $limit->object->name . ' to complete this action.');
                        }

                        if ($limit->debit) {
                            $service = new InventoryManager;
                            $count = $limit->quantity;
                            while ($count > 0) {
                                // $service->debitItem($user-)
                                $count--;
                            }
                        }
                        break;
                    case 'currency':
                }
            }

            return true;

        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }
    }
}
