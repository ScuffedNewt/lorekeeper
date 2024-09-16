<?php

namespace App\Services\Item;

use App\Models\Currency\Currency;
use App\Models\Item\Item;
use App\Models\Loot\LootTable;
use App\Models\Raffle\Raffle;
use App\Services\Service;
use DB;

class SeedService extends Service {
    /*
    |--------------------------------------------------------------------------
    | Box Service
    |--------------------------------------------------------------------------
    |
    | Handles the editing and usage of box type items.
    |
    */

    /**
     * Retrieves any data that should be used in the item tag editing form.
     *
     * @return array
     */
    public function getEditData() {
        return [
            'characterCurrencies' => Currency::where('is_character_owned', 1)->orderBy('sort_character', 'DESC')->pluck('name', 'id'),
            'items'               => Item::orderBy('name')->pluck('name', 'id'),
            'currencies'          => Currency::where('is_user_owned', 1)->orderBy('name')->pluck('name', 'id'),
            'tables'              => LootTable::orderBy('name')->pluck('name', 'id'),
            'raffles'             => Raffle::where('rolled_at', null)->where('is_active', 1)->orderBy('name')->pluck('name', 'id'),
            'months'              => [
                '1'  => 'January',
                '2'  => 'February',
                '3'  => 'March',
                '4'  => 'April',
                '5'  => 'May',
                '6'  => 'June',
                '7'  => 'July',
                '8'  => 'August',
                '9'  => 'September',
                '10' => 'October',
                '11' => 'November',
                '12' => 'December',
            ],
        ];
    }

    /**
     * Processes the data attribute of the tag and returns it in the preferred format for edits.
     *
     * @param mixed $tag
     *
     * @return mixed
     */
    public function getTagData($tag) {
        //fetch data from DB, if there is no data then set to NULL instead
        $rewards = [];
        if (isset($tag->data['rewards'])) {
            $assets = parseAssetData($tag->data['rewards']);
            foreach ($assets as $type => $a) {
                $class = getAssetModelString($type, false);
                foreach ($a as $id => $asset) {
                    $rewards[] = (object) [
                        'rewardable_type' => $class,
                        'rewardable_id'   => $id,
                        'quantity'        => $asset['quantity'],
                    ];
                }
            }
        }
        $potential_rewards = [];
        if (isset($tag->data['potential_rewards'])) {
            $assets = parseAssetData($tag->data['potential_rewards']);
            foreach ($assets as $type => $a) {
                $class = getAssetModelString($type, false);
                foreach ($a as $id => $asset) {
                    $potential_rewards[] = (object) [
                        'garden_rewardable_type' => $class,
                        'garden_rewardable_id'   => $id,
                        'garden_quantity'        => $asset['quantity'],
                    ];
                }
            }
        }
        //fetch data from DB, if there is no data then set to NULL instead
        $data['actions'] = $tag->data['actions'] ?? 1;
        $data['start_month'] = $tag->data['start_month'] ?? null;
        $data['end_month'] = $tag->data['end_month'] ?? null;
        $data['stages'] = $tag->data['stages'] ?? null;
        $data['bonus_chance'] = $tag->data['bonus_chance'] ?? null;
        $data['rewards'] = $rewards;
        $data['potential_rewards'] = $potential_rewards ?? null;
        $data['quality'] = $tag->data['quality'] ?? null;

        return $data;
    }

    /**
     * Processes the data attribute of the tag and returns it in the preferred format.
     *
     * @param array $data
     * @param mixed $tag
     *
     * @return bool
     */
    public function updateData($tag, $data) {
        DB::beginTransaction();

        try {
            if (isset($data['stage_number'])) {
                $data['stage_number'] = array_filter($data['stage_number']);
            }
            if (isset($data['stage_image'])) {
                $data['stage_image'] = array_filter($data['stage_image']);
            }
            ///
            $stages = [];
            if (isset($data['stage_number']) && !empty($data['stage_number'])) {
                // if old stages exist
                if (isset($tag->data['stages']) && !empty($tag->data['stages']) && $tag->data['stages'] != '[]') {
                    foreach ($tag->data['stages'] as $stage) {
                        // if the stage number is not contained in $data['stage_number'], then delete the old image
                        if (!in_array($stage, $data['stage_number'])) {
                            if (file_exists(public_path('images/data/seed-stages/'.$tag->id.'-'.$stage.'-image.png'))) {
                                $this->deleteImage(public_path('images/data/seed-stages'), $tag->id.'-'.$stage.'-image.png');
                            }
                        } else {
                            $stages[] = $stage;
                        } // make sure there are no duplicates
                    }
                }
                $stages = array_unique(array_merge($stages, $data['stage_number']));
                // now go thru new stages
                if (isset($stages) && !empty($stages)) {
                    foreach ($stages as $key => $stage) {
                        if (isset($data['stage_image'][$key])) {
                            $stages[] = $stage; // make sure there are no duplicates
                            $this->handleImage($data['stage_image'][$key], public_path('images/data/seed-stages'), $tag->id.'-'.$stage.'-image.png');
                        }
                    }
                }
            } else {
                if (isset($tag->data['stages']) && !empty($tag->data['stages']) && $tag->data['stages'] != '[]') {
                    foreach ($tag->data['stages'] as $stage) {
                        $this->deleteImage(public_path('images/data/seed-stages'), $tag->id.'-'.$stage.'-image.png');
                    }
                    // delete all of them that begin with the tag id (just in case)
                    // match all files that begin with the tag id
                    $files = glob(public_path('images/data/seed-stages/'.$tag->id.'-*'));
                    // iterate through the files and delete them
                    foreach ($files as $file) {
                        if (is_file($file)) {
                            unlink($file);
                        }
                    }
                }
            }

            $seedData['actions'] = $data['actions'] ?? 1;
            $seedData['start_month'] = $data['start_month'] ?? null;
            $seedData['end_month'] = $data['end_month'] ?? ($data['start_month'] ?? null);
            $seedData['stages'] = $stages ?? null;
            $seedData['bonus_chance'] = $data['bonus_chance'] ?? null;
            $seedData['quality'] = $data['quality'] ?? null;

            if (isset($data['rewardable_type'])) {
                // The data will be stored as an asset table, json_encode()d.
                // First build the asset table, then prepare it for storage.
                $assets = createAssetsArray();
                foreach ($data['rewardable_type'] as $key => $r) {
                    switch ($r) {
                        case 'Item':
                            $type = 'App\Models\Item\Item';
                            break;
                        case 'Currency':
                            $type = 'App\Models\Currency\Currency';
                            break;
                        case 'LootTable':
                            $type = 'App\Models\Loot\LootTable';
                            break;
                        case 'Raffle':
                            $type = 'App\Models\Raffle\Raffle';
                            break;
                    }
                    $asset = $type::find($data['rewardable_id'][$key]);
                    addAsset($assets, $asset, $data['quantity'][$key]);
                }
                $assets = getDataReadyAssets($assets);

                $seedData['rewards'] = $assets;
            } else {
                throw new \Exception('You must have at least one reward.');
            }

            if (isset($data['garden_rewardable_type'])) {
                // The data will be stored as an asset table, json_encode()d.
                // First build the asset table, then prepare it for storage.
                $assets = createAssetsArray();
                foreach ($data['garden_rewardable_type'] as $key => $r) {
                    switch ($r) {
                        case 'Item':
                            $type = 'App\Models\Item\Item';
                            break;
                        case 'Currency':
                            $type = 'App\Models\Currency\Currency';
                            break;
                        case 'LootTable':
                            $type = 'App\Models\Loot\LootTable';
                            break;
                        case 'Raffle':
                            $type = 'App\Models\Raffle\Raffle';
                            break;
                    }
                    $asset = $type::find($data['garden_rewardable_id'][$key]);
                    addAsset($assets, $asset, $data['garden_quantity'][$key]);
                }
                $assets = getDataReadyAssets($assets);

                $seedData['potential_rewards'] = $assets;
            }
            $tag->update(['data' => json_encode($seedData)]);

            return $this->commitReturn(true);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }
}
