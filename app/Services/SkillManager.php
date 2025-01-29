<?php

namespace App\Services;

use App\Models\Character\CharacterSkill;
use App\Models\Skill\Skill;
use Carbon\Carbon;
use DB;

class SkillManager extends Service {
    /*
        |--------------------------------------------------------------------------
        | Skill Manager
        |--------------------------------------------------------------------------
        |
        | Handles modification of user-owned skills.
        |
        */

    /**
     * Credits an skill to a character.
     *
     * @param \App\Models\Character\Character $sender
     * @param \App\Models\Character\Character $recipient
     * @param string                          $type
     * @param array                           $data
     * @param Skill                           $skill
     * @param int                             $quantity
     *
     * @return bool
     */
    public function creditSkill($sender, $recipient, $type, $data, $skill, $quantity) {
        DB::beginTransaction();

        try {
            $recipient_stack = CharacterSkill::where([
                ['character_id', '=', $recipient->id],
                ['skill_id', '=', $skill->id],
            ])->first();

            if (!$recipient_stack) {
                $data['data'] .= 'Received '.$quantity.' points for '.$skill->name.' skill. Previous Level: 0';

                $recipient_stack = CharacterSkill::create(['character_id' => $recipient->id, 'skill_id' => $skill->id, 'level' => $quantity]);
            } else {
                $data['data'] = 'Received '.$quantity.' points for '.$skill->name.' skill. Previous Level: '.$recipient_stack->level;

                $recipient_stack->level += $quantity;
                $recipient_stack->save();
            }

            if ($type && !$this->createLog($recipient->id, $sender->id, $type, $data)) {
                throw new \Exception('Failed to create log.');
            }

            return $this->commitReturn(true);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }

    /**
     * Creates a log for the skill awarding.
     *
     * @param int    $recipientId
     * @param int    $senderId
     * @param string $type
     * @param string $data
     */
    public function createLog($recipientId, $senderId, $type, $data) {
        return DB::table('character_log')->insert(
            [
                'character_id' => $recipientId,
                'sender_id'    => $senderId,
                'log'          => 'Skill Awarded ('.$type.')',
                'log_type'     => 'Skill Awarded',
                'data'         => $data['data'],
                'created_at'   => Carbon::now(),
                'updated_at'   => Carbon::now(),
            ]
        );
    }
}
