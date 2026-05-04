<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Mail\ModMail;
use App\Models\User\User;
use App\Services\MailService;
use Auth;
use Illuminate\Http\Request;

class ModMailController extends Controller {
    /**
     * Shows the mod mail index.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getIndex() {
        return view('admin.mail.index', [
            'mails' => ModMail::orderBy('id', 'DESC')->paginate(20),
        ]);
    }

    /**
     * Shows an individual mod mail.
     *
     * @param int $id
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getMail($id) {
        $mail = ModMail::findOrFail($id);

        return view('admin.mail.mail', [
            'mail' => $mail,
        ]);
    }

    /**
     * Shows the create mod mail page.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getCreateMail() {
        return view('admin.mail.create_mail', [
            'mail' => new ModMail,
            // 'users' => ['Select User'] + User::where('id', '!=', Auth::user()->id)->orderBy('id')->pluck('name', 'id')->toArray(),
            'users' => ['Select User'] + User::orderBy('id')->pluck('name', 'id')->toArray(),
        ]);
    }

    /**
     * Sends mod mail to a user.
     */
    public function postCreateMail(Request $request, MailService $service) {
        $request->validate(ModMail::$createRules);
        $data = $request->only(['user_id', 'subject', 'message', 'issue_strike', 'strike_count']);
        if ($service->createMail($data, Auth::user())) {
            flash('Mod mail sent successfully.')->success();
        } else {
            foreach ($service->errors()->getMessages()['error'] as $error) {
                flash($error)->error();
            }
        }

        return redirect()->back();
    }
}
