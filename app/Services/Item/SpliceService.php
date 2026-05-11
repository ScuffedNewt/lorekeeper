<?php

namespace App\Services\Item;

use App\Models\Item\Item;
use App\Models\Pet\Pet;
use App\Services\Service;
use DB;

class SpliceService extends Service {
    /*
    |--------------------------------------------------------------------------
    | Splice Service
    |--------------------------------------------------------------------------
    |
    | Handles the editing and usage of splice type items.
    |
    */

    /**
     * Retrieves any data that should be used in the item tag editing form.
     *
     * @return array
     */
    public function getEditData() {
        // group the variants by their $variant->pet name, and pluck the variant name and id
        $variants = Pet::whereNotNull('parent_id')->with('parent')->get()->groupBy('parent.name')->map(function ($item) {
            return $item->pluck('name', 'id');
        })->toArray();

        return [
            'variants' => $variants,
        ];
    }

    /**
     * Processes the data attribute of the tag and returns it in the preferred format.
     *
     * @param mixed $tag
     *
     * @return mixed
     */
    public function getTagData($tag) {
        $displayVariants = [];
        if (isset($tag->data['variant_ids']) && $tag->data['variant_ids']) {
            foreach ($tag->data['variant_ids'] as $variantId) {
                if ($variantId == 'default') {
                    $displayVariants[] = 'Default';
                } else {
                    $variant = Pet::find($variantId);
                    $displayVariants[] = '<a href="'.$variant->parent->url.'" target="_blank">'.$variant->name.' ('.$variant->parent->name.')</a>';
                }
            }
        }

        return [
            'variant_ids' => $tag->data['variant_ids'] ?? null,
            'variants'    => isset($tag->data['variant_ids']) ? Pet::whereIn('id', $tag->data['variant_ids'])->get() : null,
            'display'     => $displayVariants ? implode(', ', $displayVariants) : null,
        ];
    }

    /**
     * Processes the data attribute of the tag and returns it in the preferred format.
     *
     * @param mixed $tag
     * @param array $data
     *
     * @return bool
     */
    public function updateData($tag, $data) {
        DB::beginTransaction();

        try {
            $tag->data = [
                'variant_ids' => $data['variant_ids'] ?? null,
            ];

            return $this->commitReturn(true);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }
}
