<?php

namespace App\Services\Item;

use App\Models\Item\Item;
use App\Services\Service;
use DB;

class DegradableService extends Service {
    /**
     * Retrieves any data that should be used in the item tag editing form.
     *
     * @return array
     */
    public function getEditData() {
        return [
            'activities' => [
                'Prompts', 'Design Update',
            ],
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
        $itemData = [];

        $itemData['uses'] = $tag->data['uses'] ?? null;
        foreach ($tag->getEditData()['activities'] as $activity) {
            $itemData['usage_rate'][$activity] = $tag->data['usage_rate'][$activity] ?? null;
            $itemData['consumption_type'][$activity] = $tag->data['consumption_type'][$activity] ?? null;
        }

        return $itemData;
    }

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
            $itemData = [
                'uses'             => $data['uses'],
                'usage_rate'       => $data['usage_rate'],
                'consumption_type' => $data['consumption_type'],
            ];

            $tag->update(['data' => $itemData]);

            return $this->commitReturn(true);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }
}
