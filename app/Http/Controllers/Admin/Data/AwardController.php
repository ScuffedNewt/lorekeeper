<?php

namespace App\Http\Controllers\Admin\Data;

use App\Http\Controllers\Controller;
use App\Models\Award\Award;
use App\Models\Award\AwardCategory;
use App\Models\Currency\Currency;
use App\Models\Item\Item;
use App\Models\Loot\LootTable;
use App\Models\Prompt\Prompt;
use App\Models\Raffle\Raffle;
use App\Models\Rarity;
use App\Models\User\User;
use App\Services\AwardService;
use Auth;
use Illuminate\Http\Request;

class AwardController extends Controller {
    /*
    |--------------------------------------------------------------------------
    | Admin / Award Controller
    |--------------------------------------------------------------------------
    |
    | Handles creation/editing of award categories and awards.
    |
    */

    /**********************************************************************************************

        AWARD CATEGORIES

    **********************************************************************************************/

    /**
     * Shows the award category index.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getIndex() {
        return view('admin.awards.award_categories', [
            'categories' => AwardCategory::orderBy('sort', 'DESC')->get(),
        ]);
    }

    /**
     * Shows the create award category page.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getCreateAwardCategory() {
        return view('admin.awards.create_edit_award_category', [
            'category' => new AwardCategory,
        ]);
    }

    /**
     * Shows the edit award category page.
     *
     * @param int $id
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getEditAwardCategory($id) {
        $category = AwardCategory::find($id);
        if (!$category) {
            abort(404);
        }

        return view('admin.awards.create_edit_award_category', [
            'category' => $category,
        ]);
    }

    /**
     * Creates or edits an award category.
     *
     * @param App\Services\AwardService $service
     * @param int|null                  $id
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postCreateEditAwardCategory(Request $request, AwardService $service, $id = null) {
        $id ? $request->validate(AwardCategory::$updateRules) : $request->validate(AwardCategory::$createRules);
        // TODO: Clear character references in updateAwardCategory and createAwardCategory
        $data = $request->only([
            'name', 'description', 'image', 'remove_image',
        ]);
        if ($id && $service->updateAwardCategory(AwardCategory::find($id), $data, Auth::user())) {
            flash('Award Category updated successfully.')->success();
        } elseif (!$id && $category = $service->createAwardCategory($data, Auth::user())) {
            flash('Award Category created successfully.')->success();

            return redirect()->to('admin/data/award-categories/edit/'.$category->id);
        } else {
            foreach ($service->errors()->getMessages()['error'] as $error) {
                flash($error)->error();
            }
        }

        return redirect()->back();
    }

    /**
     * Gets the award category deletion modal.
     *
     * @param int $id
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getDeleteAwardCategory($id) {
        $category = AwardCategory::find($id);

        return view('admin.awards._delete_award_category', [
            'category' => $category,
        ]);
    }

    /**
     * Deletes an award category.
     *
     * @param App\Services\AwardService $service
     * @param int                       $id
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postDeleteAwardCategory(Request $request, AwardService $service, $id) {
        if ($id && $service->deleteAwardCategory(AwardCategory::find($id))) {
            flash('Category deleted successfully.')->success();
        } else {
            foreach ($service->errors()->getMessages()['error'] as $error) {
                flash($error)->error();
            }
        }

        return redirect()->to('admin/data/award-categories');
    }

    /**
     * Sorts award categories.
     *
     * @param App\Services\AwardService $service
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postSortAwardCategory(Request $request, AwardService $service) {
        if ($service->sortAwardCategory($request->get('sort'))) {
            flash('Category order updated successfully.')->success();
        } else {
            foreach ($service->errors()->getMessages()['error'] as $error) {
                flash($error)->error();
            }
        }

        return redirect()->back();
    }

    /**********************************************************************************************

        AWARDS

    **********************************************************************************************/

    /**
     * Shows the award index.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getAwardIndex(Request $request) {
        $query = Award::query();
        $data = $request->only(['award_category_id', 'name']);
        if (isset($data['award_category_id']) && $data['award_category_id'] != 'none') {
            $query->where('award_category_id', $data['award_category_id']);
        }
        if (isset($data['name'])) {
            $query->where('name', 'LIKE', '%'.$data['name'].'%');
        }

        return view('admin.awards.awards', [
            'awards'     => $query->paginate(20)->appends($request->query()),
            'categories' => ['none' => 'Any Category'] + AwardCategory::orderBy('sort', 'DESC')->pluck('name', 'id')->toArray(),
        ]);
    }

    /**
     * Shows the create award page.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getCreateAward() {
        return view('admin.awards.create_edit_award', [
            'award'       => new Award,
            'categories'  => ['none' => 'No category'] + AwardCategory::orderBy('sort', 'DESC')->pluck('name', 'id')->toArray(),
            'prompts'     => Prompt::where('is_active', 1)->orderBy('id')->pluck('name', 'id'),
            'userOptions' => User::query()->orderBy('name')->pluck('name', 'id')->toArray(),
            'rarities'    => Rarity::orderBy('rarities.sort', 'DESC')->pluck('name', 'id')->toArray(),
        ]);
    }

    /**
     * Shows the edit award page.
     *
     * @param int $id
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getEditAward($id) {
        $award = Award::find($id);
        if (!$award) {
            abort(404);
        }

        return view('admin.awards.create_edit_award', [
            'award'       => $award,
            'categories'  => ['none' => 'No category'] + AwardCategory::orderBy('sort', 'DESC')->pluck('name', 'id')->toArray(),
            'prompts'     => Prompt::where('is_active', 1)->orderBy('id')->pluck('name', 'id'),
            'userOptions' => User::query()->orderBy('name')->pluck('name', 'id')->toArray(),
            'items'       => Item::orderBy('name')->pluck('name', 'id'),
            'awards'      => Award::orderBy('name')->pluck('name', 'id'),
            'currencies'  => Currency::where('is_user_owned', 1)->orderBy('name')->pluck('name', 'id'),
            'tables'      => LootTable::orderBy('name')->pluck('name', 'id'),
            'raffles'     => Raffle::where('rolled_at', null)->where('is_active', 1)->orderBy('name')->pluck('name', 'id'),
            'rarities'    => Rarity::orderBy('rarities.sort', 'DESC')->pluck('name', 'id')->toArray(),
        ]);
    }

    /**
     * Creates or edits an award.
     *
     * @param App\Services\AwardService $service
     * @param int|null                  $id
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postCreateEditAward(Request $request, AwardService $service, $id = null) {
        $id ? $request->validate(Award::$updateRules) : $request->validate(Award::$createRules);
        // TODO: Process all new character/user holding booleans plus all new Credits information
        // TODO: Add "extension" to image processing - see WE for example

        $data = $request->only([
            'name', 'award_category_id', 'rarity_id', 'is_released', 'allow_transfer',
            'is_user_owned', 'is_character_owned', 'user_limit', 'character_limit', 'is_featured',
            'description', 'image', 'remove_image', 'uses', 'prompts', 'release',
            'credit-name', 'credit-url', 'credit-id', 'credit-role',
            // progression stuff - since we're reusing loot select we gotta refer to it as rewardable
            'progression_rewardable_id', 'progression_rewardable_type', 'progression_quantity',
            'rewardable_id', 'rewardable_type', 'quantity',
            'debit_progressions', 'allow_reclaim',
        ]);
        if ($id && $service->updateAward(Award::find($id), $data, Auth::user())) {
            flash('Award updated successfully.')->success();
        } elseif (!$id && $award = $service->createAward($data, Auth::user())) {
            flash('Award created successfully.')->success();

            return redirect()->to('admin/data/awards/edit/'.$award->id);
        } else {
            foreach ($service->errors()->getMessages()['error'] as $error) {
                flash($error)->error();
            }
        }

        return redirect()->back();
    }

    /**
     * Gets the award deletion modal.
     *
     * @param int $id
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getDeleteAward($id) {
        $award = Award::find($id);

        return view('admin.awards._delete_award', [
            'award' => $award,
        ]);
    }

    /**
     * Creates or edits an award.
     *
     * @param App\Services\AwardService $service
     * @param int                       $id
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postDeleteAward(Request $request, AwardService $service, $id) {
        if ($id && $service->deleteAward(Award::find($id))) {
            flash('Award deleted successfully.')->success();
        } else {
            foreach ($service->errors()->getMessages()['error'] as $error) {
                flash($error)->error();
            }
        }

        return redirect()->to('admin/data/awards');
    }
}
