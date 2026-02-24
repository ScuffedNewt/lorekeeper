<?php

namespace App\Services\Claymore;

use App\Models\Character\Character;
use App\Models\Character\CharacterClass;
use App\Services\CharacterManager;
use App\Services\Service;
use Illuminate\Support\Facades\DB;

class CharacterClassManager extends Service {
    /*
    |--------------------------------------------------------------------------
    | Character Class Manager
    |--------------------------------------------------------------------------
    |
    | Handles the granting and revoking of character classes to characters.
    |
    */

    /**
     * Create a class.
     *
     * @param mixed $sender
     * @param mixed $recipient
     * @param mixed $logType
     * @param mixed $data
     * @param mixed $class     CharacterClass
     * @param mixed $quantity
     *
     * @return bool|CharacterClass
     */
    public function creditClass($sender, $recipient, $logType, $data, $class, $quantity) {
        DB::beginTransaction();

        try {
            if ($recipient->logType !== 'Character') {
                throw new \Exception('Recipient must be a character.');
            }
            if ($recipient->class_id == $class->id) {
                return $this->commitReturn($class);
            }

            $recipient->class_id = $class->id;
            $recipient->save();

            if (!(new CharacterManager)->createLog($sender->id, null, $recipient->user_id, ($recipient->user_id ? null : $recipient->owner_url), $recipient->id, 'Character Class Updated', '['.$class->displayName.']', 'character')) {
                throw new \Exception('Failed to create log.');
            }

            return $this->commitReturn($class);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }
}
