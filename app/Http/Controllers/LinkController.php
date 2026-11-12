<?php

namespace App\Http\Controllers;

use App\Services\CharacterLinkService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LinkController extends Controller {
    /**
     * Accepts or rejects a link request.
     *
     * @param mixed $action
     *
     * @return response 200
     */
    public function postHandleLink(Request $request, CharacterLinkService $service, $action) {
        if (!$service->handleCharacterRelationLink($request->only('id'), $action, Auth::user())) {
            foreach ($service->errors()->getMessages()['error'] as $error) {
                flash($error)->error();
            }

            return redirect()->back();
        }

        if ($action == 'accept') {
            flash('Link request accepted successfully!', 'success');
        } elseif ($action == 'delete') {
            flash('Link request deleted successfully!', 'success');
        }

        return redirect()->back();
    }
}
