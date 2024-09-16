<?php

namespace App\Http\Controllers\Admin\Data;

use App\Http\Controllers\Controller;
use App\Models\Item\Item;
use App\Models\Pet\Pet;
use App\Models\Pet\PetCategory;
use App\Models\Pet\PetDropData;
use App\Models\Pet\PetEvolution;
use App\Models\Pet\PetLevel;
use App\Models\Pet\PetLevelPet;
use App\Models\Pet\PetVariant;
use App\Models\Pet\PetVariantDropData;
use App\Models\User\UserPet;
use App\Services\PetDropService;
use App\Services\PetService;
use Auth;
use Illuminate\Http\Request;

class PetController extends Controller {
    /*
    |--------------------------------------------------------------------------
    | Admin / Pet Controller
    |--------------------------------------------------------------------------
    |
    | Handles creation/editing of pet categories and pets.
    |
    */

    /**********************************************************************************************

        PET CATEGORIES

    **********************************************************************************************/

    /**
     * Shows the pet category index.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getIndex() {
        return view('admin.pets.pet_categories', [
            'categories' => PetCategory::orderBy('sort', 'DESC')->get(),
        ]);
    }

    /**
     * Shows the create pet category page.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getCreatePetCategory() {
        return view('admin.pets.create_edit_pet_category', [
            'category' => new PetCategory,
        ]);
    }

    /**
     * Shows the edit pet category page.
     *
     * @param int $id
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getEditPetCategory($id) {
        $category = PetCategory::find($id);
        if (!$category) {
            abort(404);
        }

        return view('admin.pets.create_edit_pet_category', [
            'category' => $category,
        ]);
    }

    /**
     * Creates or edits an pet category.
     *
     * @param App\Services\PetService $service
     * @param int|null                $id
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postCreateEditPetCategory(Request $request, PetService $service, $id = null) {
        $id ? $request->validate(PetCategory::$updateRules) : $request->validate(PetCategory::$createRules);
        $data = $request->only([
            'name', 'description', 'image', 'remove_image', 'allow_attach', 'limit',
        ]);
        if ($id && $service->updatePetCategory(PetCategory::find($id), $data, Auth::user())) {
            flash('Category updated successfully.')->success();
        } elseif (!$id && $category = $service->createPetCategory($data, Auth::user())) {
            flash('Category created successfully.')->success();

            return redirect()->to('admin/data/pet-categories/edit/'.$category->id);
        } else {
            foreach ($service->errors()->getMessages()['error'] as $error) {
                flash($error)->error();
            }
        }

        return redirect()->back();
    }

    /**
     * Gets the pet category deletion modal.
     *
     * @param int $id
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getDeletePetCategory($id) {
        $category = PetCategory::find($id);

        return view('admin.pets._delete_pet_category', [
            'category' => $category,
        ]);
    }

    /**
     * Deletes an pet category.
     *
     * @param App\Services\PetService $service
     * @param int                     $id
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postDeletePetCategory(Request $request, PetService $service, $id) {
        if ($id && $service->deletePetCategory(PetCategory::find($id))) {
            flash('Category deleted successfully.')->success();
        } else {
            foreach ($service->errors()->getMessages()['error'] as $error) {
                flash($error)->error();
            }
        }

        return redirect()->to('admin/data/pet-categories');
    }

    /**
     * Sorts pet categories.
     *
     * @param App\Services\PetService $service
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postSortPetCategory(Request $request, PetService $service) {
        if ($service->sortPetCategory($request->get('sort'))) {
            flash('Category order updated successfully.')->success();
        } else {
            foreach ($service->errors()->getMessages()['error'] as $error) {
                flash($error)->error();
            }
        }

        return redirect()->back();
    }

    /**********************************************************************************************

        PETS

    **********************************************************************************************/

    /**
     * Shows the pet index.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getPetIndex(Request $request) {
        $query = Pet::query();
        $data = $request->only(['pet_category_id', 'name']);
        if (isset($data['pet_category_id']) && $data['pet_category_id'] != 'none') {
            $query->where('pet_category_id', $data['pet_category_id']);
        }
        if (isset($data['name'])) {
            $query->where('name', 'LIKE', '%'.$data['name'].'%');
        }

        return view('admin.pets.pets', [
            'pets'       => $query->paginate(20)->appends($request->query()),
            'categories' => ['none' => 'Any Category'] + PetCategory::orderBy('name', 'DESC')->pluck('name', 'id')->toArray(),
        ]);
    }

    /**
     * Shows the create pet page.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getCreatePet() {
        return view('admin.pets.create_edit_pet', [
            'pet'        => new Pet,
            'categories' => ['none' => 'No category'] + PetCategory::orderBy('sort', 'DESC')->pluck('name', 'id')->toArray(),
        ]);
    }

    /**
     * Shows the edit pet page.
     *
     * @param int $id
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getEditPet($id) {
        $pet = Pet::find($id);
        if (!$pet) {
            abort(404);
        }

        return view('admin.pets.create_edit_pet', [
            'pet'        => $pet,
            'categories' => ['none' => 'No category'] + PetCategory::orderBy('sort', 'DESC')->pluck('name', 'id')->toArray(),
        ]);
    }

    /**
     * Creates or edits an pet.
     *
     * @param App\Services\PetService $service
     * @param int|null                $id
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postCreateEditPet(Request $request, PetService $service, $id = null) {
        $id ? $request->validate(Pet::$updateRules) : $request->validate(Pet::$createRules);
        $data = $request->only([
            'name', 'allow_transfer', 'pet_category_id', 'description', 'image', 'remove_image', 'limit',
        ]);
        if ($id && $service->updatePet(Pet::find($id), $data, Auth::user())) {
            flash('Pet updated successfully.')->success();
        } elseif (!$id && $pet = $service->createPet($data, Auth::user())) {
            flash('Pet created successfully.')->success();

            return redirect()->to('admin/data/pets/edit/'.$pet->id);
        } else {
            foreach ($service->errors()->getMessages()['error'] as $error) {
                flash($error)->error();
            }
        }

        return redirect()->back();
    }

    /**
     * Gets the pet deletion modal.
     *
     * @param int $id
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getDeletePet($id) {
        $pet = Pet::find($id);

        return view('admin.pets._delete_pet', [
            'pet' => $pet,
        ]);
    }

    /**
     * Creates or edits an pet.
     *
     * @param App\Services\PetService $service
     * @param int                     $id
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postDeletePet(Request $request, PetService $service, $id) {
        if ($id && $service->deletePet(Pet::find($id))) {
            flash('Pet deleted successfully.')->success();
        } else {
            foreach ($service->errors()->getMessages()['error'] as $error) {
                flash($error)->error();
            }
        }

        return redirect()->to('admin/data/pets');
    }

    /**********************************************************************************************

        VARIANTS

    **********************************************************************************************/

    /**
     * Gets the create / edit pet variant page.
     *
     * @param mixed      $pet_id
     * @param mixed|null $id
     */
    public function getCreateEditVariant($pet_id, $id = null) {
        return view('admin.pets._create_edit_pet_variant', [
            'pet'     => Pet::find($pet_id),
            'variant' => $id ? PetVariant::find($id) : new PetVariant,
        ]);
    }

    /**
     * Edits pet variants.
     *
     * @param App\Services\PetService $service
     * @param int                     $id
     * @param mixed                   $pet_id
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postCreateEditVariant(Request $request, PetService $service, $pet_id, $id = null) {
        $data = $request->only(['variant_name', 'variant_image', 'remove_image', 'delete']);
        if ($id && $service->editVariant(PetVariant::findOrFail($id), $data)) {
            // we dont flash in case we are deleting the variant
        } elseif (!$id && $service->createVariant(Pet::find($pet_id), $data)) {
            flash('Variant created successfully.')->success();
        } else {
            foreach ($service->errors()->getMessages()['error'] as $error) {
                flash($error)->error();
            }
        }

        return redirect()->back();
    }

    /**********************************************************************************************

        EVOLUTIONS

    **********************************************************************************************/

    /**
     * Gets the create / edit pet evolution page.
     *
     * @param mixed      $pet_id
     * @param mixed|null $id
     */
    public function getCreateEditEvolution($pet_id, $id = null) {
        return view('admin.pets._create_edit_pet_evolution', [
            'pet'       => Pet::find($pet_id),
            'evolution' => $id ? PetEvolution::find($id) : new PetEvolution,
        ]);
    }

    /**
     * Edits pet evolutions.
     *
     * @param App\Services\PetService $service
     * @param int                     $id
     * @param mixed                   $pet_id
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postCreateEditEvolution(Request $request, PetService $service, $pet_id, $id = null) {
        $data = $request->only(['evolution_name', 'evolution_image', 'evolution_stage', 'delete', 'variant_id', 'variant_image']);
        if ($id && $service->editEvolution(PetEvolution::findOrFail($id), $data)) {
            // we dont flash in case we are deleting the evolution
        } elseif (!$id && $service->createEvolution(Pet::find($pet_id), $data)) {
            flash('Evolution created successfully.')->success();
        } else {
            foreach ($service->errors()->getMessages()['error'] as $error) {
                flash($error)->error();
            }
        }

        return redirect()->back();
    }

    /**********************************************************************************************

        PET DROPS

    **********************************************************************************************/

    /**
     * Edits pet drops.
     *
     * @param mixed $id
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postEditPetDrop(Request $request, $id) {
        if (!Auth::check()) {
            abort(404);
        }
        $this->user_pet = UserPet::where('id', $id)->first();
        if (!$this->user_pet) {
            abort(404);
        }
        $drops = $this->user_pet->drops;
        if (!$request['drops_available']) {
            $request['drops_available'] = 0;
        }

        if ($drops->update(['parameters' => $request['parameters'], 'drops_available' => $request['drops_available']])) {
            flash('Pet drops updated successfully.')->success();

            return redirect()->to($this->user_pet->pageUrl(Auth::user()->id));
        } else {
            flash('Failed to update pet drops.')->error();
        }

        return redirect()->back()->withInput();
    }

    /**
     * Shows the pet drop index.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getDropIndex() {
        return view('admin.pets.pet_drops', [
            'drops' => PetDropData::orderBy('pet_id', 'ASC')->paginate(20),
        ]);
    }

    /**
     * Shows the create pet drop data page.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getCreateDrop() {
        // get pets without drop relation
        $pets = Pet::orderBy('name', 'DESC')->whereDoesntHave('dropData')->pluck('name', 'id')->toArray();

        return view('admin.pets.create_edit_drop', [
            'drop'      => new PetDropData,
            'pets'      => $pets,
            'variants'  => PetVariant::orderBy('variant_name', 'DESC')->pluck('variant_name', 'id')->toArray(),
        ]);
    }

    /**
     * Shows the edit pet drop data page.
     *
     * @param mixed $pet_id
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getEditDrop($pet_id) {
        $pet = Pet::findOrFail($pet_id);
        $petDrop = $pet->dropData;
        if (!$petDrop) {
            abort(404);
        }

        return view('admin.pets.create_edit_drop', [
            'drop'      => $petDrop,
            'pets'      => Pet::orderBy('name', 'DESC')->pluck('name', 'id')->toArray(),
            'variants'  => PetVariant::orderBy('variant_name', 'DESC')->pluck('variant_name', 'id')->toArray(),
            'items'     => Item::orderBy('name')->pluck('name', 'id'),
        ]);
    }

    /**
     * Creates or edits pet drop data.
     *
     * @param App\Services\PetDropService $service
     * @param mixed|null                  $pet_id
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postCreateEditDrop(Request $request, PetDropService $service, $pet_id = null) {
        // $id ? $request->validate(PetDropData::$updateRules) : $request->validate(PetDropData::$createRules);
        $data = $request->only([
            'pet_id', 'label', 'weight', 'drop_frequency', 'drop_interval', 'is_active', 'item_id', 'drop_name', 'is_active', 'cap',
            'rewardable_type', 'rewardable_id', 'min_quantity', 'max_quantity', 'override',
        ]);
        if ($pet_id && $service->updatePetDrop(Pet::find($pet_id)->dropData, $data, Auth::user())) {
            flash('Pet drop updated successfully.')->success();
        } elseif (!$pet_id && $drop = $service->createPetDrop($data, Auth::user())) {
            flash('Pet drop created successfully.')->success();

            return redirect()->to('admin/data/pets/drops/edit/'.$drop->pet_id);
        } else {
            foreach ($service->errors()->getMessages()['error'] as $error) {
                flash($error)->error();
            }
        }

        return redirect()->back();
    }

    /**
     * Gets the pet drop data deletion modal.
     *
     * @param int $id
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getDeleteDrop($id) {
        $drop = PetDropData::find($id);

        return view('admin.pets._delete_drop', [
            'drop' => $drop,
        ]);
    }

    /**
     * Deletes a drop.
     *
     * @param App\Services\SpeciesService $service
     * @param int                         $id
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postDeleteDrop(Request $request, SpeciesService $service, $id) {
        if ($id && $service->deleteDropData(PetDropData::find($id))) {
            flash('Drop data deleted successfully.')->success();
        } else {
            foreach ($service->errors()->getMessages()['error'] as $error) {
                flash($error)->error();
            }
        }

        return redirect()->to('admin/data/pets/drops');
    }

    /**
     * returns widget based on id.
     *
     * @param mixed $id
     */
    public function getDropWidget($id) {
        return view('admin.pets._drop_widget', [
            'drop' => PetDropData::find($id),
        ]);
    }

    /**********************************************************************************************

        PET DROP VARIANTS

    **********************************************************************************************/

    /**
     * Gets the pet drop variant creation modal.
     *
     * @param mixed $pet_id
     */
    public function getCreateVariantDrop($pet_id) {
        return view('admin.pets._create_edit_pet_variant_drop', [
            'pet'           => Pet::findOrFail($pet_id),
            'variants'      => PetVariant::where('pet_id', $pet_id)->orderBy('variant_name', 'DESC')->pluck('variant_name', 'id')->toArray(),
            'variant_drop'  => new PetVariantDropData,
        ]);
    }

    /**
     * Gets the pet drop variant edit modal.
     *
     * @param mixed $pet_id
     * @param mixed $variant_id
     */
    public function getEditVariantDrop($pet_id, $variant_id) {
        $variant = PetVariant::findOrFail($variant_id);

        return view('admin.pets._create_edit_pet_variant_drop', [
            'pet'           => Pet::findOrFail($pet_id),
            'variants'      => PetVariant::where('pet_id', $pet_id)->orderBy('variant_name', 'DESC')->pluck('variant_name', 'id')->toArray(),
            'variant'       => $variant,
            'variant_drop'  => $variant->dropData,
        ]);
    }

    /**
     * Creates or edits a pet drop variant.
     *
     * @param mixed      $pet_id
     * @param mixed|null $variant_id
     */
    public function postCreateEditVariantDrop(Request $request, PetDropService $service, $pet_id, $variant_id = null) {
        $data = $request->only([
            'variant_id', 'rewardable_type', 'rewardable_id', 'min_quantity', 'max_quantity',
        ]);

        $variant = PetVariant::find($variant_id);
        $pet = Pet::findOrFail($pet_id);

        if ($variant_id && $service->editPetVariantDrop($variant->dropData, $data)) {
            flash('Pet variant drop edited successfully.')->success();
        } elseif (!$variant_id && $drop = $service->createPetVariantDrop($data)) {
            flash('Pet variant drop created successfully.')->success();

            return redirect()->to('admin/data/pets/drops/edit/'.$pet->id);
        } else {
            foreach ($service->errors()->getMessages()['error'] as $error) {
                flash($error)->error();
            }
        }

        return redirect()->to('admin/data/pets/drops/edit/'.$pet->id);
    }

    /**
     * Gets the pet drop variant deletion modal.
     *
     * @param mixed $pet_id
     * @param mixed $variant_id
     */
    public function getDeleteVariantDrop($pet_id, $variant_id) {
        $variant = PetVariant::findOrFail($variant_id);

        return view('admin.pets._delete_pet_variant_drop', [
            'pet'       => Pet::findOrFail($pet_id),
            'variant'   => $variant,
            'drop'      => $variant->dropData,
        ]);
    }

    /**
     * Deletes a pet drop variant.
     *
     * @param mixed $pet_id
     * @param mixed $variant_id
     */
    public function postDeleteVariantDrop(Request $request, PetDropService $service, $pet_id, $variant_id) {
        $variant = PetVariant::findOrFail($variant_id);
        if ($variant_id && $service->deletePetVariantDrop($variant->dropData)) {
            flash('Pet variant drop deleted successfully.')->success();
        } else {
            foreach ($service->errors()->getMessages()['error'] as $error) {
                flash($error)->error();
            }
        }

        return redirect()->to('admin/data/pets/drops/edit/'.$pet_id);
    }

    /**********************************************************************************************

        PET LEVELS

    **********************************************************************************************/

    /**
     * Shows the pet level index.
     */
    public function getLevelIndex() {
        return view('admin.pets.levels.levels', [
            'levels' => PetLevel::orderBy('level', 'ASC')->paginate(20),
        ]);
    }

    /**
     * Shows the create level page.
     */
    public function getCreateLevel() {
        return view('admin.pets.levels.create_edit_level', [
            'level' => new PetLevel,
        ]);
    }

    /**
     * Shows the edit level page.
     *
     * @param mixed $id
     */
    public function getEditLevel($id) {
        $level = PetLevel::find($id);
        if (!$level) {
            abort(404);
        }

        return view('admin.pets.levels.create_edit_level', [
            'level' => $level,
        ]);
    }

    /**
     * Creates or edits a pet level.
     *
     * @param mixed|null $id
     */
    public function postCreateEditLevel(Request $request, PetService $service, $id = null) {
        $data = $request->only([
            'name', 'level', 'bonding_required', 'rewardable_id', 'rewardable_type', 'quantity',
        ]);
        if ($id && $service->updatePetLevel(PetLevel::find($id), $data, Auth::user())) {
            flash('Pet level updated successfully.')->success();
        } elseif (!$id && $level = $service->createPetLevel($data, Auth::user())) {
            flash('Pet level created successfully.')->success();

            return redirect()->to('admin/data/pets/levels/edit/'.$level->id);
        } else {
            foreach ($service->errors()->getMessages()['error'] as $error) {
                flash($error)->error();
            }
        }

        return redirect()->back();
    }

    /**
     * Gets the pet level deletion modal.
     *
     * @param mixed $id
     */
    public function getDeleteLevel($id) {
        $level = PetLevel::find($id);

        return view('admin.pets.levels._delete_level', [
            'level' => $level,
        ]);
    }

    /**
     * Deletes a pet level.
     *
     * @param mixed $id
     */
    public function postDeleteLevel(Request $request, PetService $service, $id) {
        if ($id && $service->deletePetLevel(PetLevel::find($id))) {
            flash('Pet level deleted successfully.')->success();
        } else {
            foreach ($service->errors()->getMessages()['error'] as $error) {
                flash($error)->error();
            }
        }

        return redirect()->to('admin/data/pets/levels');
    }

    /**
     * Loads the add pet to level modal.
     *
     * @param mixed $id
     */
    public function getAddPetToLevel($id) {
        $level = PetLevel::find($id);
        if (!$level) {
            abort(404);
        }

        return view('admin.pets.levels._add_pet_to_level', [
            'level'     => $level,
            'pets'      => Pet::orderBy('name', 'DESC')->pluck('name', 'id')->toArray(),
        ]);
    }

    /**
     * Shows the edit pet on level page.
     *
     * @param mixed $id
     */
    public function getEditPetLevel($id) {
        $petLevel = PetLevelPet::find($id);
        if (!$petLevel) {
            abort(404);
        }

        return view('admin.pets.levels.edit_pet_level_rewards', [
            'level'    => $petLevel->level,
            'petLevel' => $petLevel,
        ]);
    }

    /**
     * Adds pet(s) to a level.
     *
     * @param mixed $id
     */
    public function postAddPetToLevel(Request $request, PetService $service, $id) {
        if ($service->addPetsToLevel($request->input('pet_ids'), PetLevel::find($id))) {
            flash('Pet(s) added to level successfully.')->success();
        } else {
            foreach ($service->errors()->getMessages()['error'] as $error) {
                flash($error)->error();
            }
        }

        return redirect()->back();
    }

    /**
     * Edits the rewards for a specific pet on a level.
     *
     * @param mixed $id
     */
    public function postEditPetLevel(Request $request, PetService $service, $id) {
        $data = $request->only([
            'rewardable_id', 'rewardable_type', 'quantity',
        ]);
        if ($service->editPetLevelPetRewards(PetLevelPet::find($id), $data)) {
            flash('Pet level rewards updated successfully.')->success();
        } else {
            foreach ($service->errors()->getMessages()['error'] as $error) {
                flash($error)->error();
            }
        }

        return redirect()->back();
    }
}
