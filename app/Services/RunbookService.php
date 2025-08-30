<?php

namespace App\Services;

use App\Models\Runbook;
use Illuminate\Support\Facades\DB;

class RunbookService extends Service {
    /*
    |--------------------------------------------------------------------------
    | Runbook Service
    |--------------------------------------------------------------------------
    |
    | Handles the creation and editing of site runbooks.
    |
    */

    /**
     * Creates a site runbook.
     *
     * @param array                 $data
     * @param \App\Models\User\User $user
     *
     * @return bool|Runbook
     */
    public function createRunbook($data, $user) {
        DB::beginTransaction();

        try {
            if (isset($data['text']) && $data['text']) {
                $data['text'] = parse($data['text']);
            } else {
                $data['text'] = null;
            }
            if (!isset($data['is_public'])) {
                $data['is_public'] = 0;
            }

            $runbook = Runbook::create($data);

            return $this->commitReturn($runbook);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }

    /**
     * Updates a site runbook.
     *
     * @param array                 $data
     * @param \App\Models\User\User $user
     * @param mixed                 $runbook
     *
     * @return bool|Runbook
     */
    public function updateRunbook($runbook, $data, $user) {
        DB::beginTransaction();

        try {
            // More specific validation
            if (Runbook::where('title', $data['title'])->where('id', '!=', $runbook->id)->exists()) {
                throw new \Exception('This title has already been taken.');
            }

            if (isset($data['text']) && $data['text']) {
                $data['text'] = parse($data['text']);
            } else {
                $data['text'] = null;
            }
            if (!isset($data['is_public'])) {
                $data['is_public'] = 0;
            }

            $runbook->update($data);

            return $this->commitReturn($runbook);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }

    /**
     * Deletes a site runbook.
     *
     * @param mixed $runbook
     *
     * @return bool
     */
    public function deleteRunbook($runbook) {
        DB::beginTransaction();

        try {
            if (Runbook::where('parent_id', $runbook->id)->exists()) {
                throw new \Exception('This runbook has child runbooks and cannot be deleted.');
            }

            $runbook->delete();

            return $this->commitReturn(true);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }
}
