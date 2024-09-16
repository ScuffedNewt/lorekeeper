<?php

namespace App\Services;

use App\Models\Pet\Pet;
use App\Models\Pet\PetCategory;
use App\Models\Pet\PetEvolution;
use App\Models\Pet\PetLevel;
use App\Models\Pet\PetVariant;
use App\Models\User\UserPet;
use App\Models\User\UserPetLevel;
use DB;

class PetService extends Service {
    /*
    |--------------------------------------------------------------------------
    | Pet Service
    |--------------------------------------------------------------------------
    |
    | Handles the creation and editing of pet categories and pets.
    |
    */

    /**********************************************************************************************

        PET CATEGORIES

    **********************************************************************************************/

    /**
     * Create a category.
     *
     * @param array                 $data
     * @param \App\Models\User\User $user
     *
     * @return bool|PetCategory
     */
    public function createPetCategory($data, $user) {
        DB::beginTransaction();

        try {
            $data = $this->populateCategoryData($data);

            $image = null;
            if (isset($data['image']) && $data['image']) {
                $data['has_image'] = 1;
                $image = $data['image'];
                unset($data['image']);
            } else {
                $data['has_image'] = 0;
            }

            $category = PetCategory::create($data);

            if ($image) {
                $this->handleImage($image, $category->categoryImagePath, $category->categoryImageFileName);
            }

            return $this->commitReturn($category);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }

    /**
     * Update a category.
     *
     * @param PetCategory           $category
     * @param array                 $data
     * @param \App\Models\User\User $user
     *
     * @return bool|PetCategory
     */
    public function updatePetCategory($category, $data, $user) {
        DB::beginTransaction();

        try {
            // More specific validation
            if (PetCategory::where('name', $data['name'])->where('id', '!=', $category->id)->exists()) {
                throw new \Exception('The name has already been taken.');
            }

            $data = $this->populateCategoryData($data, $category);

            $image = null;
            if (isset($data['image']) && $data['image']) {
                $data['has_image'] = 1;
                $image = $data['image'];
                unset($data['image']);
            }

            $category->update($data);

            if ($category) {
                $this->handleImage($image, $category->categoryImagePath, $category->categoryImageFileName);
            }

            return $this->commitReturn($category);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }

    /**
     * Delete a category.
     *
     * @param PetCategory $category
     *
     * @return bool
     */
    public function deletePetCategory($category) {
        DB::beginTransaction();

        try {
            // Check first if the category is currently in use
            if (Pet::where('pet_category_id', $category->id)->exists()) {
                throw new \Exception('An pet with this category exists. Please change its category first.');
            }

            if ($category->has_image) {
                $this->deleteImage($category->categoryImagePath, $category->categoryImageFileName);
            }
            $category->delete();

            return $this->commitReturn(true);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }

    /**
     * Sorts category order.
     *
     * @param string $data
     *
     * @return bool
     */
    public function sortPetCategory($data) {
        DB::beginTransaction();

        try {
            // explode the sort array and reverse it since the order is inverted
            $sort = array_reverse(explode(',', $data));

            foreach ($sort as $key => $s) {
                PetCategory::where('id', $s)->update(['sort' => $key]);
            }

            return $this->commitReturn(true);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }

    /**********************************************************************************************

        PETS

    **********************************************************************************************/

    /**
     * Creates a new pet.
     *
     * @param array                 $data
     * @param \App\Models\User\User $user
     *
     * @return bool|Pet
     */
    public function createPet($data, $user) {
        DB::beginTransaction();

        try {
            if (isset($data['pet_category_id']) && $data['pet_category_id'] == 'none') {
                $data['pet_category_id'] = null;
            }

            if ((isset($data['pet_category_id']) && $data['pet_category_id']) && !PetCategory::where('id', $data['pet_category_id'])->exists()) {
                throw new \Exception('The selected pet category is invalid.');
            }

            $data = $this->populateData($data);

            $image = null;
            if (isset($data['image']) && $data['image']) {
                $data['has_image'] = 1;
                $image = $data['image'];
                unset($data['image']);
            } else {
                $data['has_image'] = 0;
            }

            $pet = Pet::create($data);

            if ($image) {
                $this->handleImage($image, $pet->imagePath, $pet->imageFileName);
            }

            return $this->commitReturn($pet);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }

    /**
     * Updates an pet.
     *
     * @param Pet                   $pet
     * @param array                 $data
     * @param \App\Models\User\User $user
     *
     * @return bool|Pet
     */
    public function updatePet($pet, $data, $user) {
        DB::beginTransaction();

        try {
            if (isset($data['pet_category_id']) && $data['pet_category_id'] == 'none') {
                $data['pet_category_id'] = null;
            }

            // More specific validation
            if (Pet::where('name', $data['name'])->where('id', '!=', $pet->id)->exists()) {
                throw new \Exception('The name has already been taken.');
            }
            if ((isset($data['pet_category_id']) && $data['pet_category_id']) && !PetCategory::where('id', $data['pet_category_id'])->exists()) {
                throw new \Exception('The selected pet category is invalid.');
            }

            $data = $this->populateData($data);

            $image = null;
            if (isset($data['image']) && $data['image']) {
                $data['has_image'] = 1;
                $image = $data['image'];
                unset($data['image']);
            }

            $pet->update($data);

            if ($pet) {
                $this->handleImage($image, $pet->imagePath, $pet->imageFileName);
            }

            return $this->commitReturn($pet);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }

    /**
     * Deletes an pet.
     *
     * @param Pet $pet
     *
     * @return bool
     */
    public function deletePet($pet) {
        DB::beginTransaction();

        try {
            // Check first if the pet is currently owned or if some other site feature uses it
            if (DB::table('user_pets')->where('pet_id', $pet->id)->where('count', '>', 0)->where('deleted_at', '!=', null)->exists()) {
                throw new \Exception('At least one user currently owns this pet. Please remove the pet(s) before deleting it.');
            }
            if (DB::table('loots')->where('rewardable_type', 'Pet')->where('rewardable_id', $pet->id)->exists()) {
                throw new \Exception('A loot table currently distributes this pet as a potential reward. Please remove the pet before deleting it.');
            }
            if (DB::table('prompt_rewards')->where('rewardable_type', 'Pet')->where('rewardable_id', $pet->id)->exists()) {
                throw new \Exception('A prompt currently distributes this pet as a reward. Please remove the pet before deleting it.');
            }
            if (DB::table('user_pets_logs')->where('pet_id', $pet->id)->exists()) {
                throw new \Exception('At least one log currently has this pet. Please remove the log(s) before deleting it.');
            }
            if (DB::table('shop_stock')->where('item_id', $pet->id)->where('stock_type', 'Pet')->exists()) {
                throw new \Exception('A shop currently stocks this pet. Please remove the pet before deleting it.');
            }

            // Delete character drops and drop data if they exist
            if ($pet->dropData->exists()) {
                $pet->dropData->petDrops()->delete();
                $pet->dropData->delete();
            }

            $pet->delete();

            return $this->commitReturn(true);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }

    /**********************************************************************************************

        PET VARIANTS

    **********************************************************************************************/

    /**
     * Creates a new variant for a pet.
     *
     * @param mixed $pet
     * @param mixed $data
     */
    public function createVariant($pet, $data) {
        DB::beginTransaction();

        try {
            // check name is unique
            if (PetVariant::where('variant_name', $data['variant_name'])->where('pet_id', $pet->id)->exists()) {
                throw new \Exception('The name has already been taken.');
            }

            $image = null;
            if (isset($data['variant_image']) && $data['variant_image']) {
                $data['has_image'] = 1;
                $image = $data['variant_image'];
                unset($data['variant_image']);
            } else {
                $data['has_image'] = 0;
            }

            $data['pet_id'] = $pet->id;

            $variant = PetVariant::create($data);

            if ($image) {
                $this->handleImage($image, $variant->imagePath, $variant->imageFileName);
            }

            return $this->commitReturn(true);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }

    /**
     * Edits the variants on a pet.
     *
     * @param mixed $variant
     * @param mixed $data
     */
    public function editVariant($variant, $data) {
        DB::beginTransaction();

        try {
            // check name is unique
            if (PetVariant::where('variant_name', $data['variant_name'])->where('pet_id', $variant->pet->id)->where('id', '!=', $variant->id)->exists()) {
                throw new \Exception('The name has already been taken.');
            }

            if (isset($data['remove_image'])) {
                if ($variant && $variant->has_image && $data['remove_image']) {
                    $data['has_image'] = 0;
                    $this->deleteImage($variant->imagePath, $variant->imageFileName);
                }
                unset($data['remove_image']);
            }

            $image = null;
            if (isset($data['variant_image']) && $data['variant_image']) {
                $data['has_image'] = 1;
                $image = $data['variant_image'];
                unset($data['variant_image']);
            }

            $variant->update([
                'variant_name' => $data['variant_name'],
                'has_image'    => $data['has_image'] ?? $variant->has_image,
            ]);

            if ($image) {
                $this->handleImage($image, $variant->imagePath, $variant->imageFileName);
            }

            if (isset($data['delete']) && $data['delete']) {
                // check that no user pets exist with this variant before deleting
                if (UserPet::where('variant_id', $variant->id)->exists()) {
                    throw new \Exception('At least one user pet currently is this variant. Please remove the pet(s) before deleting it.');
                }

                // delete image
                if ($variant->has_image) {
                    $this->deleteImage($variant->imagePath, $variant->imageFileName);
                }
                $variant->delete();
                flash('Variant deleted successfully.')->success();
            } else {
                flash('Variant updated successfully.')->success();
            }

            return $this->commitReturn(true);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }

    /**********************************************************************************************

        PET EVOLUTIONS

    **********************************************************************************************/

    /**
     * Creates a pet evolution.
     *
     * @param mixed $pet
     * @param mixed $data
     */
    public function createEvolution($pet, $data) {
        DB::beginTransaction();

        try {
            if (!isset($data['evolution_name']) || !$data['evolution_name']) {
                throw new \Exception('Please enter a valid evolution name.');
            }
            // check name is unique
            if (PetEvolution::where('evolution_name', $data['evolution_name'])->where('pet_id', $pet->id)->exists()) {
                throw new \Exception('The name has already been taken.');
            }

            $image = null;
            if (isset($data['evolution_image']) && $data['evolution_image']) {
                $data['has_image'] = 1;
                $image = $data['evolution_image'];
                unset($data['evolution_image']);
            }

            $data['pet_id'] = $pet->id;

            if (!$image) {
                throw new \Exception('Please upload an image for this evolution.');
            }

            $evolution = PetEvolution::create($data);

            if ($image) {
                $this->handleImage($image, $evolution->imagePath, $evolution->imageFileName);
            }

            return $this->commitReturn(true);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }

    /**
     * Edits the evolutions on a pet.
     *
     * @param mixed $evolution
     * @param mixed $data
     */
    public function editEvolution($evolution, $data) {
        DB::beginTransaction();

        try {
            if (!isset($data['evolution_name']) || !$data['evolution_name']) {
                throw new \Exception('Please enter a valid evolution name.');
            }
            if (PetEvolution::where('evolution_name', $data['evolution_name'])->where('pet_id', $evolution->pet->id)->where('id', '!=', $evolution->id)->exists()) {
                throw new \Exception('The name has already been taken.');
            }
            if (!isset($data['evolution_stage']) || !$data['evolution_stage']) {
                throw new \Exception('Please enter a valid evolution stage.');
            }

            $image = null;
            if (isset($data['evolution_image']) && $data['evolution_image']) {
                $image = $data['evolution_image'];
                unset($data['evolution_image']);
            }

            $evolution->update([
                'evolution_name'  => $data['evolution_name'],
                'evolution_stage' => $data['evolution_stage'],
            ]);

            if ($image) {
                $this->handleImage($image, $evolution->imagePath, $evolution->imageFileName);
            }

            // variant images
            if (isset($data['variant_id']) && $data['variant_id']) {
                foreach ($data['variant_id'] as $key => $variant_id) {
                    $variant = PetVariant::find($variant_id);
                    if ($variant) {
                        $variant_image = null;
                        if (isset($data['variant_image'][$key]) && $data['variant_image'][$key]) {
                            $variant_image = $data['variant_image'][$key];
                            unset($data['variant_image'][$key]);
                        }

                        if ($variant_image) {
                            $this->handleImage($variant_image, $evolution->variantImageDirectory, $evolution->variantImageFileName($variant->id));
                        }
                    }
                }
            }

            if (isset($data['delete']) && $data['delete']) {
                // check that no user pets exist with this evolution before deleting
                if (UserPet::where('evolution_id', $evolution->id)->exists()) {
                    throw new \Exception('At least one user pet currently is this evolution. Please remove the pet(s) before deleting it.');
                }
                // delete image
                $this->deleteImage($evolution->imagePath, $evolution->imageFileName);
                // delete all variant images
                foreach ($evolution->pet->variants as $variant) {
                    // check if file exists
                    if ($evolution->variantImageExists($variant->id)) {
                        $this->deleteImage($evolution->variantImageDirectory, $evolution->variantImageFileName($variant->id));
                    }
                }
                $evolution->delete();
                flash('Evolution deleted successfully.')->success();
            } else {
                flash('Evolution updated successfully.')->success();
            }

            return $this->commitReturn(true);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }

    /**********************************************************************************************

        PET LEVELS

    **********************************************************************************************/

    /**
     * Creates a new pet level.
     *
     * @param array                 $data
     * @param \App\Models\User\User $user
     *
     * @return bool|PetLevel
     */
    public function createPetLevel($data, $user) {
        DB::beginTransaction();

        try {
            if (!isset($data['level']) || !$data['level']) {
                throw new \Exception('Please enter a valid level.');
            }
            // check that level is unique
            if (PetLevel::where('level', $data['level'])->exists()) {
                throw new \Exception('The level has already been taken.');
            }

            $level = PetLevel::create($data);

            return $this->commitReturn($level);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }

    /**
     * Updates a pet level.
     *
     * @param PetLevel              $level
     * @param array                 $data
     * @param \App\Models\User\User $user
     *
     * @return bool|PetLevel
     */
    public function updatePetLevel($level, $data, $user) {
        DB::beginTransaction();

        try {
            if (!isset($data['level']) || !$data['level']) {
                throw new \Exception('Please enter a valid level.');
            }
            // check that level is unique
            if (PetLevel::where('level', $data['level'])->where('id', '!=', $level->id)->exists()) {
                throw new \Exception('The level has already been taken.');
            }

            $rewards = createAssetsArray();
            if (isset($data['rewardable_id']) && $data['rewardable_id']) {
                foreach ($data['rewardable_id'] as $key => $rewardable_id) {
                    $reward = findReward($data['rewardable_type'][$key], $rewardable_id);
                    $rewards[$reward->assetType] = [
                        'rewardable_id'   => $rewardable_id,
                        'rewardable_type' => $data['rewardable_type'][$key],
                        'quantity'        => $data['quantity'][$key],
                    ];
                }
            }

            $level->update([
                'name'             => $data['name'],
                'level'            => $data['level'],
                'bonding_required' => $data['bonding_required'],
                'rewards'          => json_encode($rewards),
            ]);

            return $this->commitReturn($level);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }

    /**
     * Deletes a pet level.
     *
     * @param PetLevel $level
     *
     * @return bool
     */
    public function deletePetLevel($level) {
        DB::beginTransaction();

        try {
            // make sure no pets are using this level
            if (UserPetLevel::where('bonding_level', $level->level)->exists()) {
                throw new \Exception('At least one user pet is currently using this level. Please remove the pet(s) before deleting it.');
            }

            $level->delete();

            return $this->commitReturn(true);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }

    /**
     * Adds pets to a level.
     *
     * @param mixed $pet_ids
     * @param mixed $level
     */
    public function addPetsToLevel($pet_ids, $level) {
        DB::beginTransaction();

        try {
            $level->pets()->delete();

            $pet_ids = array_unique($pet_ids);
            foreach ($pet_ids as $pet_id) {
                $pet = Pet::find($pet_id);
                $level->pets()->create([
                    'pet_id' => $pet_id,
                ]);
            }

            return $this->commitReturn(true);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }

    /**
     * Adds rewards to a pet on a level.
     *
     * @param mixed $petLevel
     * @param mixed $data
     */
    public function editPetLevelPetRewards($petLevel, $data) {
        DB::beginTransaction();

        try {
            $rewards = createAssetsArray();
            if (isset($data['rewardable_id']) && $data['rewardable_id']) {
                foreach ($data['rewardable_id'] as $key => $rewardable_id) {
                    $reward = findReward($data['rewardable_type'][$key], $rewardable_id);
                    $rewards[$reward->assetType] = [
                        'rewardable_id'   => $rewardable_id,
                        'rewardable_type' => $data['rewardable_type'][$key],
                        'quantity'        => $data['quantity'][$key],
                    ];
                }
            }

            $petLevel->update([
                'rewards' => json_encode($rewards),
            ]);

            return $this->commitReturn($petLevel);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }

    /**
     * Handle category data.
     *
     * @param array            $data
     * @param PetCategory|null $category
     *
     * @return array
     */
    private function populateCategoryData($data, $category = null) {
        if (isset($data['description']) && $data['description']) {
            $data['parsed_description'] = parse($data['description']);
        }

        if (!isset($data['allow_attach'])) {
            $data['allow_attach'] = 0;
            $data['limit'] = null;
        }
        // If attachments are allowed, but no limit is set, set it to null.
        if (isset($data['allow_attach']) && $data['allow_attach'] && !isset($data['limit'])) {
            $data['limit'] = null;
        }

        if (isset($data['remove_image'])) {
            if ($category && $category->has_image && $data['remove_image']) {
                $data['has_image'] = 0;
                $this->deleteImage($category->categoryImagePath, $category->categoryImageFileName);
            }
            unset($data['remove_image']);
        }

        return $data;
    }

    /**
     * Processes user input for creating/updating an pet.
     *
     * @param array $data
     * @param Pet   $pet
     *
     * @return array
     */
    private function populateData($data, $pet = null) {
        if (isset($data['description']) && $data['description']) {
            $data['parsed_description'] = parse($data['description']);
        }

        // If attachments are allowed, but no limit is set, set it to null.
        if (isset($data['allow_attach']) && $data['allow_attach'] && !isset($data['limit'])) {
            $data['limit'] = null;
        }

        if (!isset($data['allow_transfer'])) {
            $data['allow_transfer'] = 0;
        }

        if (isset($data['remove_image'])) {
            if ($pet && $pet->has_image && $data['remove_image']) {
                $data['has_image'] = 0;
                $this->deleteImage($pet->imagePath, $pet->imageFileName);
            }
            unset($data['remove_image']);
        }

        return $data;
    }
}
