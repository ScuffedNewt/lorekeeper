<?php

namespace App\Services;

use App\Models\Shop\Shop;
use App\Models\Shop\ShopLimit;
use Illuminate\Support\Facades\DB;

class ShopService extends Service {
    /*
    |--------------------------------------------------------------------------
    | Shop Service
    |--------------------------------------------------------------------------
    |
    | Handles the creation and editing of shops and shop stock.
    |
    */

    /**********************************************************************************************

        SHOPS

    **********************************************************************************************/

    /**
     * Creates a new shop.
     *
     * @param array                 $data
     * @param \App\Models\User\User $user
     *
     * @return bool|Shop
     */
    public function createShop($data, $user) {
        DB::beginTransaction();

        try {
            $data = $this->populateShopData($data);

            $image = null;
            if (isset($data['image']) && $data['image']) {
                $data['has_image'] = 1;
                $data['hash'] = randomString(10);
                $image = $data['image'];
                unset($data['image']);
            } else {
                $data['has_image'] = 0;
            }

            $data['is_timed_shop'] = isset($data['is_timed_shop']);

            $shop = Shop::create($data);

            if ($image) {
                $this->handleImage($image, $shop->shopImagePath, $shop->shopImageFileName);
            }

            return $this->commitReturn($shop);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }

    /**
     * Updates a shop.
     *
     * @param Shop                  $shop
     * @param array                 $data
     * @param \App\Models\User\User $user
     *
     * @return bool|Shop
     */
    public function updateShop($shop, $data, $user) {
        DB::beginTransaction();

        try {
            // More specific validation
            if (Shop::where('name', $data['name'])->where('id', '!=', $shop->id)->exists()) {
                throw new \Exception('The name has already been taken.');
            }

            $data = $this->populateShopData($data, $shop);

            $image = null;
            if (isset($data['image']) && $data['image']) {
                $data['has_image'] = 1;
                $data['hash'] = randomString(10);
                $image = $data['image'];
                unset($data['image']);
            }

            $data['is_timed_shop'] = isset($data['is_timed_shop']);

            $shop->update($data);

            if ($shop) {
                $this->handleImage($image, $shop->shopImagePath, $shop->shopImageFileName);
            }

            return $this->commitReturn($shop);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }

    /**
     * Updates shop stock.
     *
     * @param Shop                  $shop
     * @param array                 $data
     * @param \App\Models\User\User $user
     *
     * @return bool|Shop
     */
    public function updateShopStock($shop, $data, $user) {
        DB::beginTransaction();

        try {
            if (!$data['stock_type']) {
                throw new \Exception('Please select a stock type.');
            }
            if (!$data['item_id']) {
                throw new \Exception('You must select an item.');
            }

            $shop->stock()->create([
                'shop_id'                  => $shop->id,
                'item_id'                  => $data['item_id'],
                'currency_id'              => $data['currency_id'],
                'cost'                     => $data['cost'],
                'use_user_bank'            => isset($data['use_user_bank']),
                'use_character_bank'       => isset($data['use_character_bank']),
                'is_fto'                   => isset($data['is_fto']),
                'is_limited_stock'         => isset($data['is_limited_stock']),
                'quantity'                 => isset($data['is_limited_stock']) ? $data['quantity'] : 0,
                'purchase_limit'           => $data['purchase_limit'] ?? 0,
                'purchase_limit_timeframe' => isset($data['purchase_limit']) ? $data['purchase_limit_timeframe'] : null,
                'stock_type'               => $data['stock_type'],
                'is_visible'               => $data['is_visible'] ?? 0,
                'restock'                  => $data['restock'] ?? 0,
                'restock_quantity'         => isset($data['restock']) && isset($data['quantity']) ? $data['quantity'] : 1,
                'restock_interval'         => $data['restock_interval'] ?? 2,
                'range'                    => $data['range'] ?? 0,
                'is_timed_stock'           => isset($data['is_timed_stock']),
                'start_at'                 => $data['start_at'],
                'end_at'                   => $data['end_at'],
            ]);

            return $this->commitReturn($shop);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }

    /**
     * Updates shop stock.
     *
     * @param array                 $data
     * @param \App\Models\User\User $user
     * @param mixed                 $stock
     *
     * @return \App\Models\Shop\Shop|bool
     */
    public function editShopStock($stock, $data, $user) {
        DB::beginTransaction();

        try {
            if (!$data['stock_type']) {
                throw new \Exception('Please select a stock type.');
            }
            if (!$data['item_id']) {
                throw new \Exception('You must select an item.');
            }

            $stock->update([
                'shop_id'                  => $stock->shop->id,
                'item_id'                  => $data['item_id'],
                'currency_id'              => $data['currency_id'],
                'cost'                     => $data['cost'],
                'use_user_bank'            => isset($data['use_user_bank']),
                'use_character_bank'       => isset($data['use_character_bank']),
                'is_fto'                   => isset($data['is_fto']),
                'is_limited_stock'         => isset($data['is_limited_stock']),
                'quantity'                 => isset($data['is_limited_stock']) ? $data['quantity'] : 0,
                'purchase_limit'           => $data['purchase_limit'],
                'purchase_limit_timeframe' => $data['purchase_limit_timeframe'],
                'stock_type'               => $data['stock_type'],
                'is_visible'               => $data['is_visible'] ?? 0,
                'restock'                  => $data['restock'] ?? 0,
                'restock_quantity'         => isset($data['restock']) && isset($data['quantity']) ? $data['quantity'] : 1,
                'restock_interval'         => $data['restock_interval'] ?? 2,
                'range'                    => $data['range'] ?? 0,
                'disallow_transfer'        => $data['disallow_transfer'] ?? 0,
                'is_timed_stock'           => isset($data['is_timed_stock']),
                'start_at'                 => $data['start_at'],
                'end_at'                   => $data['end_at'],
            ]);

            return $this->commitReturn($stock);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }

    public function deleteStock($stock) {
        DB::beginTransaction();

        try {
            $stock->delete();

            return $this->commitReturn(true);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }

    /**
     * Deletes a shop.
     *
     * @param Shop $shop
     *
     * @return bool
     */
    public function deleteShop($shop) {
        DB::beginTransaction();

        try {
            // Delete shop stock
            $shop->stock()->delete();

            if ($shop->has_image) {
                $this->deleteImage($shop->shopImagePath, $shop->shopImageFileName);
            }
            $shop->delete();

            return $this->commitReturn(true);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }

    /**
     * Sorts shop order.
     *
     * @param array $data
     *
     * @return bool
     */
    public function sortShop($data) {
        DB::beginTransaction();

        try {
            // explode the sort array and reverse it since the order is inverted
            $sort = array_reverse(explode(',', $data));

            foreach ($sort as $key => $s) {
                Shop::where('id', $s)->update(['sort' => $key]);
            }

            return $this->commitReturn(true);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }

    public function restrictShop($data, $id) {
        DB::beginTransaction();

        try {
            if (!isset($data['is_restricted'])) {
                $data['is_restricted'] = 0;
            }

            $shop = Shop::find($id);
            $shop->is_restricted = $data['is_restricted'];
            $shop->save();

            $shop->limits()->delete();

            if (isset($data['item_id'])) {
                foreach ($data['item_id'] as $key => $type) {
                    ShopLimit::create([
                        'shop_id'       => $shop->id,
                        'item_id'       => $type,
                    ]);
                }
            }

            return $this->commitReturn(true);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }

    /**
     * Processes user input for creating/updating a shop.
     *
     * @param array $data
     * @param Shop  $shop
     *
     * @return array
     */
    private function populateShopData($data, $shop = null) {
        if (isset($data['description']) && $data['description']) {
            $data['parsed_description'] = parse($data['description']);
        }
        $data['is_active'] = isset($data['is_active']);
        $data['is_staff'] = isset($data['is_staff']);
        $data['use_coupons'] = isset($data['use_coupons']);
        $data['allowed_coupons'] ??= null;

        if (isset($data['remove_image'])) {
            if ($shop && $shop->has_image && $data['remove_image']) {
                $data['has_image'] = 0;
                $this->deleteImage($shop->shopImagePath, $shop->shopImageFileName);
            }
            unset($data['remove_image']);
        }

        return $data;
    }
}
