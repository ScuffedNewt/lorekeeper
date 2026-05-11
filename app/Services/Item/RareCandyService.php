<?php

namespace App\Services\Item;

use App\Models\Item\Item;
use App\Services\Service;
use DB;

class RareCandyService extends Service {
    /*
    |--------------------------------------------------------------------------
    | RareCandy Service
    |--------------------------------------------------------------------------
    |
    | Handles the editing and usage of Rare Candy type items.
    |
    */

    /**
     * Retrieves any data that should be used in the item tag editing form.
     *
     * @return array
     */
    public function getEditData() {
        return [

        ];
    }

    /**
     * Processes the data attribute of the tag and returns it in the preferred format.
     *
     * @param string $tag
     *
     * @return mixed
     */
    public function getTagData($tag) {}

    /**
     * Processes the data attribute of the tag and returns it in the preferred format.
     *
     * @param string $tag
     * @param array  $data
     *
     * @return bool
     */
    public function updateData($tag, $data) {
        DB::beginTransaction();

        try {
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
            return $this->commitReturn(true);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }
}
