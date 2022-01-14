<?php

namespace App\Http\Controllers\Users;

use Auth;
use Illuminate\Http\Request;

use App\Models\User;
use App\Models\User\UserFriend;
use App\Models\User\UserBlock;

use App\Services\InteractionManager;

use App\Http\Controllers\Controller;

class FriendController extends Controller
{
    //

    /**
     * Gets the index where the user can view all of their friends.
     */
    public function getIndex()
    {
        $friends = Auth::user()->friends;
        return view('user.friends.friend_index', [
            'user' => Auth::check() ? Auth::user() : null,
            'friends' => UserFriend::where('initiator_id', Auth::user()->id)->orWhere('recipient_id', Auth::user()->id)->get()
        ]);
    }

    /**
     * Initiates a friend request
     */
    public function sendFriendRequest(Request $request, InteractionManager $service, $id)
    {
        if($service->createFriendRequest(Auth::user(), $id)) {
            flash('Request sent successfully.')->success();
        }
        else {
            foreach($service->errors()->getMessages()['error'] as $error) flash($error)->error();
        }
        return redirect()->back();
    }
}
