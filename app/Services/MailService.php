<?php

namespace App\Services;

use App\Models\Mail\ModMail;
use App\Models\Mail\UserMail;
use App\Models\User\User;
use DB;
use Notifications;
use Settings;

class MailService extends Service {
    /**
     * Creates mod mail.
     *
     * @param array $data
     * @param mixed $staff
     *
     * @return ModMail
     */
    public function createMail($data, $staff) {
        DB::beginTransaction();

        try {
            $user = User::find($data['user_id']);
            if (!$user) {
                throw new \Exception('User not found');
            }

            if (!isset($data['issue_strike']) || !$data['issue_strike']) {
                $data['issue_strike'] = false;
                $data['strike_count'] = 0;
            }
            $mail = ModMail::create([
                'staff_id'              => $staff->id,
                'user_id'               => $user->id,
                'subject'               => $data['subject'],
                'message'               => $data['message'],
                'issue_strike'          => $data['issue_strike'],
                'strike_count'          => $data['strike_count'],
                'previous_strike_count' => $user->settings->strike_count,
                'seen'                  => false,
            ]);

            $user->settings->update([
                'strike_count' => $data['issue_strike'] ? $user->settings->strike_count + $data['strike_count'] : $user->settings->strike_count,
            ]);

            if (Settings::get('max_strike_count') && $user->settings->strike_count >= Settings::get('max_strike_count')) {
                // ban user
                $service = new UserService;
                $service->ban(['ban_reason' => 'Banned for exceeding the maximum strike count.'], $user, $staff);
            }

            return $this->commitReturn(true);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }

    /**
     * Creates user mail.
     *
     * @param array $data
     * @param mixed $sender
     *
     * @return UserMail
     */
    public function createUserMail($data, $sender) {
        DB::beginTransaction();

        try {
            $recipient = User::find($data['recipient_id']);
            if (!$recipient) {
                throw new \Exception('Recipient not found');
            }
            if ($recipient->id == $sender->id) {
                throw new \Exception('You cannot send mail to yourself.');
            }

            $mail = UserMail::create([
                'sender_id'    => $sender->id,
                'recipient_id' => $recipient->id,
                'subject'      => $data['subject'],
                'message'      => $data['message'],
                'seen'         => false,
                'parent_id'    => $data['parent_id'],
            ]);

            // send a notification
            Notifications::create('DIRECT_MESSAGE_RECEIVED', $recipient, [
                'sender_url'  => $sender->url,
                'sender_name' => $sender->name,
                'subject'     => $mail->subject,
                'mail_id'     => $mail->id,
            ]);

            return $this->commitReturn(true);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }
}
