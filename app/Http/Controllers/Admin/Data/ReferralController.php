<?php

namespace App\Http\Controllers\Admin\Data;

use Illuminate\Http\Request;

use Auth;

use App\Models\Item\Item;
use App\Models\Currency\Currency;
use App\Models\Loot\LootTable;
use App\Models\Raffle\Raffle;

use App\Http\Controllers\Controller;
use App\Models\Referral;
use Illuminate\Support\Facades\DB;

class ReferralController extends Controller {
  /*
    |--------------------------------------------------------------------------
    | Admin / Referral Controller
    |--------------------------------------------------------------------------
    |
    | Handles creation/editing of referral conditions
    |
    */

  /**
   * Shows the prompt category index.
   *
   * @param  \Illuminate\Http\Request  $request
   * @return \Illuminate\Contracts\Support\Renderable
   */
  public function getIndex(Request $request) {

    return view('admin.referrals.referrals', [
      'referrals' => Referral::orderBy('referral_count')->get()->paginate(20)
    ]);
  }

  /**
   * Shows the create referral page.
   *
   * @return \Illuminate\Contracts\Support\Renderable
   */
  public function getCreate() {
    return view('admin.referrals.create_edit', [
      'referral' => new Referral,
      'items' => Item::orderBy('name')->pluck('name', 'id'),
      'currencies' => Currency::where('is_user_owned', 1)->orderBy('name')->pluck('name', 'id'),
      'tables' => LootTable::orderBy('name')->pluck('name', 'id'),
      'raffles' => Raffle::where('rolled_at', null)->where('is_active', 1)->orderBy('name')->pluck('name', 'id'),
    ]);
  }

  /**
   * Shows the edit referral page.
   *
   * @param  int  $id
   * @return \Illuminate\Contracts\Support\Renderable
   */
  public function getEdit($id) {
    $referral = Referral::find($id);
    if (!$referral) abort(404);
    return view('admin.referrals.create_edit', [
      'referral' => $referral,
      'items' => Item::orderBy('name')->pluck('name', 'id'),
      'currencies' => Currency::where('is_user_owned', 1)->orderBy('name')->pluck('name', 'id'),
      'tables' => LootTable::orderBy('name')->pluck('name', 'id'),
      'raffles' => Raffle::where('rolled_at', null)->where('is_active', 1)->orderBy('name')->pluck('name', 'id'),
    ]);
  }

  /**
   * Creates or edits a referral.
   *
   * @param  \Illuminate\Http\Request    $request
   * @param  App\Services\PromptService  $service
   * @param  int|null                    $id
   * @return \Illuminate\Http\RedirectResponse
   */
  public function postCreateEdit(Request $request, $id = null) {

    $data = $request->only([
      'referral_count', 'days_active'
    ]);
    $data['data'] = encodeForDataColumn($request->only(['rewardable_type', 'rewardable_id', 'quantity']));
    $data['is_active'] = $request->get('is_active') !== null;
    $data['on_every'] = $request->get('on_every') !== null;

    DB::beginTransaction();
    try {
      if ($id) $referral = Referral::find($id)->update($data);
      else $referral = Referral::create($data);
      DB::commit();

      flash('Referral saved successfully.')->success();
      return redirect()->to('admin/data/referrals/edit/' . ($id ?? $referral->id));
    } catch (\Exception $e) {
      DB::rollback();
      flash($e->getMessage())->error();
      return redirect()->back();
    }
  }

  /**
   * Gets the prompt deletion modal.
   *
   * @param  int  $id
   * @return \Illuminate\Contracts\Support\Renderable
   */
  public function getDelete($id) {
    $referral = Referral::find($id);
    return view('admin.referrals._delete', [
      'referral' => $referral,
    ]);
  }

  /**
   * Deletes a prompt.
   *
   * @param  \Illuminate\Http\Request    $request
   * @param  App\Services\PromptService  $service
   * @param  int                         $id
   * @return \Illuminate\Http\RedirectResponse
   */
  public function postDelete(Request $request, $id) {
    try {
      Referral::find($id)->delete();
      DB::commit();
      flash('Prompt deleted successfully.')->success();
    } catch (\Exception $e) {
      DB::rollback();
      flash($e->getMessage())->error();
    }
    return redirect()->to('admin/data/referrals');
  }
}
