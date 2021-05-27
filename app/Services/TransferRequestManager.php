<?php namespace App\Services;

use Carbon\Carbon;
use App\Services\Service;

use DB;
use Auth;
use Config;
use Notifications;

use App\Models\TransferRequest;
use App\Models\User\User;
use App\Models\Item\Item;
use App\Models\Currency\Currency;
use App\Models\User\UserItem;
use App\Models\User\UserCurrency;

use App\Services\InventoryManager;
use App\Services\CurrencyManager;

class TransferRequestManager extends Service
{
    public function rejectTransferRequest($data, $user)
    {
        DB::beginTransaction();

        try {

            $t = TransferRequest::find($data['id']);
            $t->status = 'Rejected';
            $t->staff_id = $user->id;
            $t->staff_comments = $data['staff_comments'];
            $t->save();

            $items = json_decode($t->items);
            if(isset($items->currency_id[0])) {

                $usercurrency = UserCurrency::where('currency_id', $items->currency_id[0])->where('user_id', $t->sender_id)->first();

                $quantity = 0;
                foreach($items->quantity as $q) $quantity += $q;

                DB::table('user_currencies')->where('user_id', $t->sender_id)->where('currency_id', $items->currency_id[0])->update(['quantity' => $usercurrency->quantity + $quantity]);
            }

            Notifications::create('TRANSFER_REQUEST_DENIED', $t->user, [
                'recipient_name' => $t->recipient->displayname,
                'staff_name' => $user->displayname,
                'transfer_id' => $t->id
            ]);

            return $this->commitReturn(true);
        } catch(\Exception $e) { 
            $this->setError('error', $e->getMessage());
        }
        return $this->rollbackReturn(false);
    }

    public function approveTransferRequest($data, $user)
    {
        DB::beginTransaction();

        try {
            $t = TransferRequest::find($data['id']);
            $t->status = 'Accepted';
            $t->staff_id = $user->id;
            $t->staff_comments = $data['staff_comments'];
            $t->save();

            Notifications::create('TRANSFER_REQUEST_ACCEPTED', $t->user, [
                'recipient_name' => $t->recipient->displayname,
                'staff_name' => $user->displayname,
                'transfer_id' => $t->id
            ]);

            $items = json_decode($t->items);

            if(isset($items->stack_id[0])) {

                $service = new InventoryManager;

                foreach($items->stack_id as $key => $item) {
                    $userItem = UserItem::find($item);
                    if($service->moveStack($t->user, $t->recipient, 'User Transfer', ['data' => 'Transferred by ' . $t->user->displayname], $userItem, $items->quantity[$key])) {
                        if($userItem->transfer_count < $items->quantity[$key]) throw new \Exception("Cannot return more items than was held. (".$item.")");
                        $userItem->transfer_count -= $items->quantity[$key];
                        $userItem->save();
                        flash('Item transferred successfully.')->success();
                    }
                    else {
                        foreach($service->errors()->getMessages()['error'] as $error) flash($error)->error();
                    }
                }
            }
            elseif(isset($items->currency_id[0])) {
                $service = new CurrencyManager;

                $quantity = 0;
                foreach($items->quantity as $q) $quantity += $q;

                if($service->creditCurrency($t->user, $t->recipient, 'User Transfer', null, Currency::find($items->currency_id[0]), $quantity)) {
                    flash('Currency transferred successfully.')->success();
                }
                else {
                    foreach($service->errors()->getMessages()['error'] as $error) flash($error)->error();
                }
            }

            return $this->commitReturn(true);
        } catch(\Exception $e) { 
            $this->setError('error', $e->getMessage());
        }
        return $this->rollbackReturn(false);
    }

}
