<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\LimitService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LimitController extends Controller {
    /**
     * Creates or edits an objects limits.
     *
     * @param App\Services\LimitService $service
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postCreateEditLimits(Request $request, LimitService $service) {
        $data = $request->only([
            'object_model', 'object_id', 'limit_type', 'limit_id', 'quantity', 'debit', 'is_unlocked',
        ]);
        if ($service->editLimits($data['object_model'], $data['object_id'], $data, Auth::user())) {
            flash('Limits updated successfully.')->success();
        } else {
            foreach ($service->errors()->getMessages()['error'] as $error) {
                flash($error)->error();
            }
        }

        return redirect()->back();
    }
}
