<?php

namespace App\Http\Controllers\Users;

use App\Http\Controllers\Controller;
use App\Models\Mail\ModMail;
use App\Models\Mail\UserMail;
use App\Models\User\User;
use App\Services\MailService;
use Auth;
use Illuminate\Http\Request;

class MailController extends Controller {
    /**
     * Shows the mail index.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getIndex() {
        if (!Auth::check()) {
            abort(404);
        }

        return view('home.mail.mail_index', [
            'mails'  => ModMail::where('user_id', Auth::user()->id)->orderBy('created_at', 'desc')->get(),
            'inbox'  => UserMail::where('recipient_id', Auth::user()->id)->orderBy('created_at', 'desc')->get(),
            'outbox' => UserMail::where('sender_id', Auth::user()->id)->orderBy('created_at', 'desc')->get(),
        ]);
    }

    /**
     * Shows a specific mod mail.
     *
     * @param int $id
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getMail($id) {
        if (!Auth::check()) {
            abort(404);
        }
        $mail = ModMail::findOrFail($id);

        if (!$mail->seen && $mail->user_id == Auth::user()->id) {
            $mail->update(['seen' => 1]);
        }

        return view('home.mail.mail', [
            'mail' => $mail,
        ]);
    }

    /**
     * Shows a specific user mail.
     *
     * @param int $id
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getUserMail($id) {
        if (!Auth::check()) {
            abort(404);
        }
        $mail = UserMail::findOrFail($id);

        if (Auth::user()->id != $mail->sender_id && Auth::user()->id != $mail->recipient_id) {
            abort(403);
        }

        if (!$mail->seen && $mail->recipient_id == Auth::user()->id) {
            $mail->update(['seen' => 1]);
        }

        return view('home.mail.user_mail', [
            'mail' => $mail,
        ]);
    }

    /**
     * Shows the create user mail page.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getCreateUserMail() {
        return view('home.mail.create_user_mail', [
            'mail'  => new UserMail,
            'users' => ['Select User'] + User::orderBy('id')->where('id', '!=', Auth::user()->id)->pluck('name', 'id')->toArray(),
        ]);
    }

    /**
     * Sends mail from one user to another.
     */
    public function postCreateUserMail(Request $request, MailService $service) {
        $request->validate(UserMail::$createRules);
        $data = $request->only(['recipient_id', 'subject', 'message', 'parent_id']);
        if ($service->createUserMail($data, Auth::user())) {
            flash('Message sent successfully.')->success();
        } else {
            foreach ($service->errors()->getMessages()['error'] as $error) {
                flash($error)->error();
            }
        }

        if (!isset($data['parent_id'])) {
            return redirect()->back();
        } else {
            $child = UserMail::where('parent_id', $data['parent_id'])->latest('id')->first();

            return redirect('inbox/view/'.$child->id);
        }
    }
}
