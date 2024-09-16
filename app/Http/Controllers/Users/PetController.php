<?php

namespace App\Http\Controllers\Users;

use App\Http\Controllers\Controller;
use App\Models\Character\Character;
use App\Models\Item\ItemTag;
use App\Models\Pet\Pet;
use App\Models\Pet\PetCategory;
use App\Models\Pet\PetDrop;
use App\Models\Pet\PetVariant;
use App\Models\User\User;
use App\Models\User\UserItem;
use App\Models\User\UserPet;
use App\Services\PetDropService;
use App\Services\PetManager;
use Auth;
use Illuminate\Http\Request;

class PetController extends Controller {
    /*
    |--------------------------------------------------------------------------
    | Pet Controller
    |--------------------------------------------------------------------------
    |
    | Handles pet management for the user.
    |
    */

    /**
     * Shows the user's pet page.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getIndex() {
        $categories = PetCategory::orderBy('sort', 'DESC')->get();
        $pets = count($categories) ? Auth::user()->pets()->orderByRaw('FIELD(pet_category_id,'.implode(',', $categories->pluck('id')->toArray()).')')->orderBy('pet_name')->get()->groupBy('pet_category_id') : Auth::user()->pets()->orderBy('pet_name')->get()->groupBy('pet_category_id');

        return view('home.pets', [
            'categories'        => $categories->keyBy('id'),
            'pets'              => $pets,
            'userOptions'       => User::visible()->where('id', '!=', Auth::user()->id)->orderBy('name')->pluck('name', 'id')->toArray(),
            'user'              => Auth::user(),
            'userCreditOptions' => ['' => 'Select User'] + User::visible()->orderBy('name')->get()->pluck('verified_name', 'id')->toArray(),
        ]);
    }

    /**
     * Shows the pet stack modal.
     *
     * @param int $id
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getStack(Request $request, $id) {
        $stack = UserPet::withTrashed()->where('id', $id)->with('pet')->first();
        $chara = Character::myo()->where('user_id', $stack->user_id)->pluck('slug', 'id');

        $readOnly = $request->get('read_only') ?: ((Auth::check() && $stack && !$stack->deleted_at && ($stack->user_id == Auth::user()->id || Auth::user()->hasPower('edit_inventories'))) ? 0 : 1);

        // if the tag has data['variant_ids'], only show if the userpet->pet has a variant that matches
        $tags = ItemTag::where('tag', 'splice')->where('is_active', 1)->get();
        $tags = $tags->filter(function ($tag) use ($stack) {
            if (isset($tag->data['variant_ids'])) {
                // if "default" is an option, then it's always available
                if (in_array('default', $tag->data['variant_ids'])) {
                    return true;
                }

                return PetVariant::whereIn('id', $tag->data['variant_ids'])->where('pet_id', $stack->pet_id)->exists();
            } else {
                return true;
            }
        })->pluck('item_id');
        $splices = UserItem::where('user_id', $stack->user_id)->whereIn('item_id', $tags)->where('count', '>', 0)->with('item')->get()->pluck('item.name', 'id');

        return view('home._pet_stack', [
            'stack'             => $stack,
            'chara'             => $chara,
            'user'              => Auth::user(),
            'userOptions'       => ['' => 'Select User'] + User::visible()->where('id', '!=', $stack ? $stack->user_id : 0)->orderBy('name')->get()->pluck('verified_name', 'id')->toArray(),
            'readOnly'          => $readOnly,
            'splices'           => $splices,
            'userCreditOptions' => ['' => 'Select User'] + User::visible()->orderBy('name')->get()->pluck('verified_name', 'id')->toArray(),
        ]);
    }

    /**
     * Transfers an pet stack to another user.
     *
     * @param App\Services\PetManager $service
     * @param int                     $id
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postTransfer(Request $request, PetManager $service, $id) {
        if ($service->transferStack(Auth::user(), User::visible()->where('id', $request->get('user_id'))->first(), UserPet::where('id', $id)->first())) {
            flash('Pet transferred successfully.')->success();
        } else {
            foreach ($service->errors()->getMessages()['error'] as $error) {
                flash($error)->error();
            }
        }

        return redirect()->back();
    }

    /**
     * Deletes an pet stack.
     *
     * @param App\Services\PetManager $service
     * @param int                     $id
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postDelete(Request $request, PetManager $service, $id) {
        if ($service->deleteStack(Auth::user(), UserPet::where('id', $id)->first())) {
            flash('Pet deleted successfully.')->success();
        } else {
            foreach ($service->errors()->getMessages()['error'] as $error) {
                flash($error)->error();
            }
        }

        return redirect()->back();
    }

    /**
     * Names an pet.
     *
     * @param App\Services\CharacterManager $service
     * @param mixed                         $id
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postName(Request $request, PetManager $service, $id) {
        if ($service->nameStack(UserPet::find($id), $request->get('name'))) {
            flash('Pet named successfully.')->success();
        } else {
            foreach ($service->errors()->getMessages()['error'] as $error) {
                flash($error)->error();
            }
        }

        return redirect()->back();
    }

    /**
     * Attaches an pet.
     *
     * @param App\Services\CharacterManager $service
     * @param mixed                         $id
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postAttach(Request $request, PetManager $service, $id) {
        if ($service->attachStack(UserPet::find($id), $request->get('id'))) {
            flash('Pet attached successfully.')->success();
        } else {
            foreach ($service->errors()->getMessages()['error'] as $error) {
                flash($error)->error();
            }
        }

        return redirect()->back();
    }

    /**
     * Detaches an pet.
     *
     * @param App\Services\CharacterManager $service
     * @param mixed                         $id
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postDetach(Request $request, PetManager $service, $id) {
        if ($service->detachStack(UserPet::find($id))) {
            flash('Pet detached successfully.')->success();
        } else {
            foreach ($service->errors()->getMessages()['error'] as $error) {
                flash($error)->error();
            }
        }

        return redirect()->back();
    }

    /**
     * Changes variant.
     *
     * @param App\Services\CharacterManager $service
     * @param mixed                         $id
     * @param mixed                         $isStaff
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postVariant(Request $request, PetManager $service, $id, $isStaff = false) {
        $pet = UserPet::find($id);
        if ($service->editVariant($request->input('variant_id'), $pet, $request->input('stack_id'), $request->input('is_staff'))) {
            flash('Pet variant changed successfully.')->success();
        } else {
            foreach ($service->errors()->getMessages()['error'] as $error) {
                flash($error)->error();
            }
        }

        return redirect()->back();
    }

    /**
     * Changes evolution.
     *
     * @param App\Services\CharacterManager $service
     * @param mixed                         $id
     * @param mixed                         $isStaff
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postEvolution(Request $request, PetManager $service, $id, $isStaff = false) {
        $pet = UserPet::find($id);
        if ($service->editEvolution($request->input('evolution_id'), $pet, $request->input('stack_id'), $request->input('is_staff'))) {
            flash('Pet evolution changed successfully.')->success();
        } else {
            foreach ($service->errors()->getMessages()['error'] as $error) {
                flash($error)->error();
            }
        }

        return redirect()->back();
    }

    /**
     * Shows the pet selection widget.
     *
     * @param int $id
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getSelector($id) {
        return view('widgets._pet_select', [
            'user' => Auth::user(),
        ]);
    }

    /**
     * Shows a pet's indiviudal page.
     *
     * @param mixed $id
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getPetPage($id) {
        $pet = UserPet::findOrFail($id);
        $user = $pet->user;

        // if the tag has data['variant_ids'], only show if the userpet->pet has a variant that matches
        $tags = ItemTag::where('tag', 'splice')->where('is_active', 1)->get();
        $tags = $tags->filter(function ($tag) use ($pet) {
            if (isset($tag->data['variant_ids'])) {
                if (in_array('default', $tag->data['variant_ids'])) {
                    return true;
                }

                return PetVariant::whereIn('id', $tag->data['variant_ids'])->where('pet_id', $pet->pet_id)->exists();
            } else {
                return true;
            }
        })->pluck('item_id');
        $splices = UserItem::where('user_id', $user->id)->whereIn('item_id', $tags)->where('count', '>', 0)->with('item')->get()->pluck('item.name', 'id');

        return view('user.pet', [
            'user'        => $user,
            'pet'         => $pet,
            'drops'       => $pet->drops,
            'userOptions' => User::where('id', '!=', $user->id)->orderBy('name')->pluck('name', 'id')->toArray(),
            'logs'        => $user->getPetLogs(),
            'splices'     => $splices,
        ]);
    }

    /**
     * Claims pet drops.
     *
     * @param App\Services\PetDropService $service
     * @param mixed                       $id
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postClaimPetDrops(PetDropService $service, $id) {
        $pet = UserPet::findOrFail($id);
        if (!Auth::check() || $pet->user_id != Auth::user()->id) {
            abort(404);
        }
        if (!$pet->drops) {
            abort(404);
        }
        if ($service->claimPetDrops($pet)) {
            flash('Drops claimed successfully.')->success();
        } else {
            foreach ($service->errors()->getMessages()['error'] as $error) {
                flash($error)->error();
            }
        }

        return redirect()->back();
    }

    /**
     * Claims pet drops.
     *
     * @param App\Services\PetDropService $service
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postClaimAllPetDrops(PetDropService $service) {
        $user_pet_ids = UserPet::where('user_id', Auth::user()->id)->pluck('id');
        $pet_drops = PetDrop::whereIn('user_pet_id', $user_pet_ids)->where('drops_available', '>', 0)->pluck('user_pet_id');
        $pets = UserPet::whereIn('id', $pet_drops)->get();

        $rewards = createAssetsArray();
        foreach ($pets as $pet) {
            if ($assets = $service->claimPetDrops($pet, false)) {
                $rewards = mergeAssetsArrays($rewards, $assets);
            } else {
                foreach ($service->errors()->getMessages()['error'] as $error) {
                    flash($error)->error();
                }
            }
        }
        if (createRewardsString($rewards)) {
            flash('You received: '.createRewardsString($rewards))->info();
        } else {
            flash('No drops to claim.')->info();
        }
        flash('Drops claimed successfully.')->success();

        return redirect()->back();
    }

    /**
     * Post custom image.
     *
     * @param mixed $id
     */
    public function postCustomImage($id, Request $request, PetManager $service) {
        $pet = UserPet::findOrFail($id);
        $data = $request->only(['image', 'remove_image', 'artist_id', 'artist_url', 'remove_credit']);

        if (!Auth::user()->isStaff) {
            abort(404);
        }

        if ($service->editCustomImage($pet, $data)) {
            flash('Pet image updated successfully.')->success();
        } else {
            foreach ($service->errors()->getMessages()['error'] as $error) {
                flash($error)->error();
            }
        }

        return redirect()->back();
    }

    /**
     * Unique image.
     *
     * @param mixed $id
     */
    public function postDescription($id, Request $request, PetManager $service) {
        $pet = UserPet::findOrFail($id);

        if ($service->editCustomImageDescription($pet, $request->only(['description']))) {
            flash('Pet custom image description updated successfully.')->success();
        } else {
            foreach ($service->errors()->getMessages()['error'] as $error) {
                flash($error)->error();
            }
        }

        return redirect()->back();
    }

    /**
     * Bonds with a pet.
     *
     * @param mixed $id
     */
    public function postBond($id, Request $request, PetManager $service) {
        $pet = UserPet::findOrFail($id);

        if ($service->bondPet($pet, Auth::user())) {
            flash('Pet bonded successfully.')->success();
        } else {
            foreach ($service->errors()->getMessages()['error'] as $error) {
                flash($error)->error();
            }
        }

        return redirect()->back();
    }
}
