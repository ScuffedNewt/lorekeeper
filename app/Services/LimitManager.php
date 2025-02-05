<?php

namespace App\Services;

use App\Models\Element\Typing;
use App\Models\Limit\Limit;
use App\Models\Submission\Submission;
use App\Models\User\UserItem;
use App\Services\Stat\StatManager;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

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
     * @param mixed      $object
     * @param mixed      $is_unlock
     * @param mixed|null $data
     * @param mixed|null $logType
     * @param mixed|null $logData
     */
    public function checkLimits($object, $is_unlock = false, $data = null, $logType = null, $logData = null) {
        try {
            $user = Auth::user();

            $limits = Limit::hasLimits($object) ? Limit::getLimits($object) : [];
            if (!count($limits)) {
                return true;
            }

            if ($limits->first()->is_unlocked) {
                if ($user->unlockedLimits()->where('object_model', get_class($object))->where('object_id', $object->id)->exists()) {
                    return true;
                }
            }

            // if the limit is not unlocked, check if it is auto unlocked
            if (!$is_unlock && $limits->first()->is_unlocked && !$limits->first()->is_auto_unlocked) {
                throw new \Exception(($limits->first()->object->displayName ?? $limits->first()->object->name).' requires manual unlocking!');
            }

            $plucked_stacks = [];
            foreach ($limits as $limit) {
                switch ($limit->limit_type) {
                    case 'prompt':
                        if (!$limit->quantity) {
                            continue 2;
                        }

                        // check at least quantity of prompts has been approved
                        if (Submission::where('user_id', $user->id)->where('status', 'Approved')->where('prompt_id', $limit->limit_id)->count() < $limit->quantity) {
                            throw new \Exception('You have not completed the prompt '.$limit->limit->displayName.' enough times to complete this action.');
                        }
                        break;
                    case 'item':
                        if (!$limit->quantity) {
                            continue 2;
                        }

                        if (!$user->items()->where('item_id', $limit->limit_id)->sum('count') >= $limit->quantity) {
                            throw new \Exception('You do not have enough of the item '.$limit->object->name.' to complete this action.');
                        }

                        if ($limit->debit) {
                            $stacks = UserItem::where('user_id', $user->id)->where('item_id', $limit->limit_id)->orderBy('count', 'asc')->get(); // asc because pop() removes from the end

                            $count = $limit->quantity;
                            while ($count > 0) {
                                $stack = $stacks->pop();
                                $quantity = $stack->count >= $count ? $count : $stack->count;
                                $count -= $quantity;
                                $plucked_stacks[$stack->id] = $quantity;
                            }
                        }
                        break;
                    case 'currency':
                        if (!$limit->quantity) {
                            continue 2;
                        }

                        if (DB::table('user_currencies')->where('user_id', $user->id)->where('currency_id', $limit->limit_id)->value('quantity') < $limit->quantity) {
                            throw new \Exception('You do not have enough '.$limit->limit->displayName.' to complete this action.');
                        }

                        if ($limit->debit) {
                            $service = new CurrencyManager;
                            if (!$service->debitCurrency($user, null, $logType ?? 'Limit Requirements', $logData ?? 'Used in '.$limit->object->displayName.' limit requirements.', $limit->limit, $limit->quantity)) {
                                foreach ($service->errors()->getMessages()['error'] as $error) {
                                    flash($error)->error();
                                }
                                throw new \Exception('Currency could not be removed.');
                            }
                        }
                        break;
                    case 'dynamic':
                        if (!$this->checkDynamicLimit($limit, $user)) {
                            throw new \Exception('You do not meet the requirements to complete this action.');
                        }
                        break;
                    case 'class':
                        if (!$data || !isset($data['character_ids']) || !count($data['character_ids'])) {
                            throw new \Exception('A character must be selected or attached to complete this action.');
                        }

                        $characters = $user->characters()->whereIn('id', $data['character_ids'])->get()->filter(function ($character) use ($limit) {
                            return $character->class_id == $limit->limit_id;
                        });

                        // < 1 just in case the creator forgot to set a limit quantity
                        if ($characters->count() < 1 || $characters->count() < $limit->quantity) {
                            throw new \Exception('You do not have enough characters of the class '.$limit->limit->name.' to complete this action.');
                        }
                        break;
                    case 'stat':
                        if (!$data || !isset($data['character_ids']) || !count($data['character_ids'])) {
                            throw new \Exception('A character must be selected or attached to complete this action.');
                        }

                        $characters = $user->characters()->whereIn('id', $data['character_ids'])->get()->filter(function ($character) use ($limit) {
                            return $character->stats()->where('stat_id', $limit->limit_id)->first()->current_count >= $limit->quantity;
                        });

                        if ($characters->count() < 1) {
                            throw new \Exception('You do not have enough characters with the required stat '.$limit->limit->name.' to complete this action.');
                        }

                        if ($limit->debit) {
                            $service = new StatManager;
                            foreach ($characters as $character) {
                                $stat = $character->stats()->where('stat_id', $limit->limit_id)->first();
                                $quantity = $stat->current_count - $limit->quantity;
                                if (!$service->editCharacterStatCurrentCount($stat, $character, $quantity, $logType ?? 'Limit Requirements', $logData ?? 'Used in '.$limit->object->displayName.' limit requirements.')) {
                                    foreach ($service->errors()->getMessages()['error'] as $error) {
                                        flash($error)->error();
                                    }
                                    throw new \Exception('Stats could not be removed from '.$character->displayName.'.');
                                }
                            }
                        }
                        break;
                    case 'character_level':
                        if (!$data || !isset($data['character_ids']) || !count($data['character_ids'])) {
                            throw new \Exception('A character must be selected or attached to complete this action.');
                        }

                        $characters = $user->characters()->whereIn('id', $data['character_ids'])->get()->filter(function ($character) use ($limit) {
                            return $character->level->current_level >= $limit->limit->level;
                        });

                        if ($characters->count() < 1) {
                            throw new \Exception('You do not have enough characters with the required level '.$limit->limit->level.' to complete this action.');
                        }
                        break;
                    case 'user_level':
                        if ($user->level->current_level < $limit->limit->level) {
                            throw new \Exception('You do not meet the required level '.$limit->limit->level.' to complete this action.');
                        }
                        break;
                    case 'element':
                        if (!$data || !isset($data['character_ids']) || !count($data['character_ids'])) {
                            throw new \Exception('A character must be selected or attached to complete this action.');
                        }

                        $characters = $user->characters()->whereIn('id', $data['character_ids'])->get()->filter(function ($character) use ($limit) {
                            return Typing::where('typing_model', get_class($character->image))->where('typing_id', $character->image->id)->first()?->elements()?->where('id', $limit->limit_id)->count();
                        });

                        if ($characters->count() < 1) {
                            throw new \Exception('You do not have enough characters with the required element '.$limit->limit->name.' to complete this action.');
                        }
                        break;
                    default:
                        throw new \Exception('Limit type '.$limit->limit_type.' is not supported.');
                        break;
                }
            }

            if (count($plucked_stacks)) {
                $inventoryManager = new InventoryManager;
                $type = $logType ?? 'Limit Requirements';
                $data = [
                    'data' => $logData ?? 'Used in '.($limit->object->displayName ?? $limit->object->name).'\'s limit requirements.',
                ];

                foreach ($plucked_stacks as $id=>$quantity) {
                    $stack = UserItem::find($id);
                    if (!$inventoryManager->debitStack($user, $type, $data, $stack, $quantity)) {
                        throw new \Exception('Items could not be removed.');
                    }
                }
            }

            if ($limits->first()->is_unlocked && $limits->first()->is_auto_unlocked || $is_unlock) {
                $user->unlockedLimits()->create([
                    'object_model' => get_class($object),
                    'object_id'    => $object->id,
                ]);
            } elseif (!$is_unlock && $limits->first()->is_unlocked && !$limits->first()->is_auto_unlocked) {
                throw new \Exception(($limits->first()->object->displayName ?? $limits->first()->object->name).' requires manual unlocking!');
            }

            return true;
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }
    }

    /**
     * checks a dynamic limit.
     *
     * @param mixed $limit
     * @param mixed $user
     */
    private function checkDynamicLimit($limit, $user) {
        try {
            return $limit->limit->evaluate();
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }
    }
}
