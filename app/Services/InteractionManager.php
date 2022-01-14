<?php namespace App\Services;

use Carbon\Carbon;
use App\Services\Service;

use DB;
use Auth;
use Config;
use Notifications;

use Illuminate\Support\Arr;
use App\Models\User\User;
use App\Models\User\UserFriend;
use App\Models\User\UserBlock;

class InteractionManager extends Service
{
    /**
     * Creates a friend request and notification for the recipient.
     */
    public function createFriendRequest($initiator, $recipient_id)
    {
        DB::beginTransaction();

        try {

            $recipient = User::find($recipient_id);
            if(!$recipient) throw new \Exception('Recipient not found.');

            $request = UserFriend::create([
                'initiator_id' => $initiator->id,
                'recipient_id' => $recipient_id,
                'recipient_approval' => 'Pending',
                'created_at' => Carbon::now(),
                'approved_at' => null
            ]);

            Notifications::create('FRIEND_REQUEST', $recipient, [
                'initiator_url' => $initiator->url,
                'initiator_name' => $initiator->name,
            ]);

            return $this->commitReturn(true);
        } catch(\Exception $e) { 
            $this->setError('error', $e->getMessage());
        }
        return $this->rollbackReturn(false);
    }
}
