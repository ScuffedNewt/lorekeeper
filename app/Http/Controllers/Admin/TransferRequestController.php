<?php

namespace App\Http\Controllers\Admin;

use Auth;
use Config;
use Illuminate\Http\Request;

use App\Models\TransferRequest;
use App\Models\Item\Item;
use App\Models\User\UserItem;
use App\Models\User\UserCurrency;

use App\Services\InventoryManager;
use App\Services\TransferRequestManager;

use App\Http\Controllers\Controller;

class TransferRequestController extends Controller
{
    /**
     * Shows the transfer index page.
     *
     * @param  string  $status
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getTransferRequestIndex(Request $request, $status = null)
    {
        $transfers = TransferRequest::where('status', $status ? ucfirst($status) : 'Pending');
        $data = $request->only(['sort']);
        if(isset($data['sort']))
        {
            switch($data['sort']) {
                case 'newest':
                    $transfers->sortNewest();
                    break;
                case 'oldest':
                    $transfers->sortOldest();
                    break;
            }
        }
        else $transfers->sortOldest();
        return view('admin.transfers.index', [
            'transfers' => $transfers->paginate(30)->appends($request->query()),
        ]);
    }

    /**
     * Shows the transfer detail page.
     *
     * @param  int  $id
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getTransferRequest($id)
    {
        $transfer = TransferRequest::where('id', $id)->first();
        if(!$transfer) abort(404);

        $items = json_decode($transfer->items);
        if(isset($items->stack_id[0])) {
            $userThing = UserItem::find($items->stack_id[0]);
        }
        elseif(isset($items->currency_id[0])) {
            $userThing = UserCurrency::where('currency_id', $items->currency_id[0])->where('user_id', $transfer->sender_id)->first();
        }

        $quantity = 0;
        foreach($items->quantity as $q) $quantity += $q;

        return view('admin.transfers.transfer', [
            'transfer' => $transfer,
            'object' => $userThing,
            'quantity' => $quantity
        ]);
    }


    /**
     * Creates a new transfer.
     *
     * @param  \Illuminate\Http\Request        $request
     * @param  App\Services\TransferRequestManager  $service
     * @param  int                             $id
     * @param  string                          $action
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postTransferRequest(Request $request, TransferRequestManager $service, $id, $action)
    {
        $data = $request->only(['staff_comments' ]);
        if($action == 'reject' && $service->rejectTransferRequest($request->only(['staff_comments']) + ['id' => $id], Auth::user())) {
            flash('Transfer Request rejected successfully.')->success();
        }
        elseif($action == 'approve' && $service->approveTransferRequest($data + ['id' => $id], Auth::user())) {
            flash('Transfer Request approved successfully.')->success();
        }
        else {
            foreach($service->errors()->getMessages()['error'] as $error) flash($error)->error();
        }
        return redirect()->back();
    }

    /***
     * GETS MEMBER SIDE
     * 
     */
    public function getTransfer($id)
    {
        $transfer = TransferRequest::where('id', $id)->first();
        if(!$transfer) abort(404);

        $items = json_decode($transfer->items);
        if(isset($items->stack_id[0])) {
            $userThing = UserItem::find($items->stack_id[0]);
        }
        elseif(isset($items->currency_id[0])) {
            $userThing = UserCurrency::find($items->currency_id[0]);
        }

        $quantity = 0;
        foreach($items->quantity as $q) $quantity += $q;

        return view('home.transfer', [
            'transfer' => $transfer,
            'object' => $userThing,
            'quantity' => $quantity,
            'user' => $transfer->user
        ]);
    }

    /**
     * Shows the user's transfer log.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getIndex(Request $request)
    {
        $transfers = TransferRequest::where('sender_id', Auth::user()->id);
        $type = $request->get('type');
        if(!$type) $type = 'Pending';

        $transfers = $transfers->where('status', ucfirst($type));

        return view('home.transfers', [
            'transfers' => $transfers->orderBy('id', 'DESC')->paginate(20)->appends($request->query()),
        ]);
    }
}
