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
use App\Models\User\UserItem;

use App\Services\InventoryManager;

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

            $service = new InventoryManager;
            $items = json_decode($t->items);
            foreach($items->stack_id as $key => $item) {
                if($service->transferStack($t->user, $t->recipient, UserItem::find($item), $items->quantity[$key])) {
                    flash('Item transferred successfully.')->success();
                }
            }

            return $this->commitReturn(true);
        } catch(\Exception $e) { 
            $this->setError('error', $e->getMessage());
        }
        return $this->rollbackReturn(false);
    }

}
