<?php

namespace App\Http\Controllers\Admin\Data;

use Illuminate\Http\Request;

use Auth;

use App\Models\Slot\Slot;
use App\Models\Slot\SlotCategory;
use App\Models\Loot\LootTable;
use App\Models\Raffle\Raffle;
use App\Models\Currency\Currency;
use App\Models\Recipe\Recipe;

use App\Models\Recipe\CraftingSlot;

use App\Services\SlotService;

use App\Http\Controllers\Controller;

class SlotController extends Controller
{

    public function getIndex()
    {
        return view('admin.slots.index', [
            'slots' => CraftingSlot::all(),
        ]);
    }

    public function getCreateSlot()
    {
        return view('admin.slots.create_edit_slot', [
            'slot' => new CraftingSlot,
            'currencies' => Currency::where('is_user_owned', 1)->orderBy('sort_user', 'DESC')->pluck('name', 'id'),
        ]);
    }

        /**
     * Shows the edit slot page.
     *
     * @param  int  $id
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getEditSlot($id)
    {
        $slot = CraftingSlot::find($id);
        if(!$slot) abort(404);
        return view('admin.slots.create_edit_slot', [
            'slot' => $slot,
            'currencies' => Currency::where('is_user_owned', 1)->orderBy('sort_user', 'DESC')->pluck('name', 'id'),
        ]);
    }

        /**
     * Creates or edits an slot.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  App\Services\SlotService  $service
     * @param  int|null                  $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postCreateEditSlot(Request $request, SlotService $service, $id = null)
    {
        $data = $request->only(['free', 'currency_id', 'slot_cost']);
        if($id && $service->updateSlot(CraftingSlot::find($id), $data, Auth::user())) {
            flash('Slot updated successfully.')->success();
        }
        else if (!$id && $slot = $service->createSlot($data, Auth::user())) {
            flash('Slot created successfully.')->success();
            return redirect()->to('admin/data/slots/edit/'.$slot->id);
        }
        else {
            foreach($service->errors()->getMessages()['error'] as $error) flash($error)->error();
        }
        return redirect()->back();
    }

    /**
     * Gets the slot deletion modal.
     *
     * @param  int  $id
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getDeleteSlot($id)
    {
        $slot = CraftingSlot::find($id);
        return view('admin.slots._delete_slot', [
            'slot' => $slot,
        ]);
    }

    /**
     * Creates or edits an slot.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  App\Services\SlotService  $service
     * @param  int                       $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postDeleteSlot(Request $request, SlotService $service, $id)
    {
        if($id && $service->deleteSlot(CraftingSlot::find($id))) {
            flash('Slot deleted successfully.')->success();
        }
        else {
            foreach($service->errors()->getMessages()['error'] as $error) flash($error)->error();
        }
        return redirect()->to('admin/data/slots');
    }
}