<?php

namespace App\Services;

use App\Models\Limit\Limit;
use Auth;
use DB;
use Log;

class LimitService extends Service {
    /*
    |--------------------------------------------------------------------------
    | Limit Service
    |--------------------------------------------------------------------------
    |
    | Handles the creation and editing of limits on objects
    |
    */

    /**********************************************************************************************

        LIMITS

    **********************************************************************************************/

    /**
     * edits an limits on an object.
     *
     * @param mixed     $data
     * @param bool|true $log
     * @param mixed     $object_model
     * @param mixed     $object_id
     */
    public function editLimits($object_model, $object_id, $data, $log = true) {
        DB::beginTransaction();

        try {
            // first delete all limits for the object
            $object = $object_model::find($object_id);
            if (!$object) {
                throw new \Exception('Object not found.');
            }

            $limits = Limit::hasLimits($object) ? Limit::getLimits($object) : [];
            if (count($limits) > 0) {
                $limits->each(function ($limit) {
                    $limit->delete();
                });
            }
            if (count($limits) > 0) {
                flash('Deleted '.count($limits).' old limits.')->success();
            }

            if (isset($data['limit_type'])) {
                foreach ($data['limit_type'] as $key => $type) {
                    $limit = new Limit([
                        'object_model' => $object_model,
                        'object_id'    => $object_id,
                        'limit_type'   => $data['limit_type'][$key],
                        'limit_id'     => $data['limit_id'][$key],
                        'quantity'     => $data['quantity'][$key],
                        'debit'        => $data['debit'][$key] == 'no' ? 0 : 1,
                        'is_unlocked'  => $data['is_unlocked'] == 'no' ? 0 : 1,
                    ]);

                    if (!$limit->save()) {
                        throw new \Exception('Failed to save limit.');
                    }
                }
            }

            // log the action
            if ($log && !$this->logAdminAction(Auth::user(), 'Edited Limits', 'Edited '.$object->displayName.' limits')) {
                throw new \Exception('Failed to log admin action.');
            }

            return $this->commitReturn(true);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }
}
