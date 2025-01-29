<?php

namespace App\Services;

use App\Facades\Notifications;
use App\Models\Character\Character;
use App\Models\Character\CharacterSkill;
use App\Models\Skill\Skill;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

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
     * Grants skills to multiple characters.
     *
     * @param mixed $data
     * @param mixed $staff
     *
     * @return bool
     */
    public function grantSkills($data, $staff) {
        DB::beginTransaction();

        try {
            foreach ($data['quantities'] as $q) {
                if ($q == 0) {
                    throw new \Exception('All quantities must not be 0.');
                }
            }

            // Process names
            $characters = Character::find($data['character_ids']);
            if (count($characters) != count($data['character_ids'])) {
                throw new \Exception('An invalid character was selected.');
            }

            $keyed_quantities = [];
            array_walk($data['skill_ids'], function ($id, $key) use (&$keyed_quantities, $data) {
                if ($id != null && !in_array($id, array_keys($keyed_quantities), true)) {
                    $keyed_quantities[$id] = $data['quantities'][$key];
                }
            });

            // Process skils
            $skills = Skill::find($data['skill_ids']);
            if (!count($skills)) {
                throw new \Exception('No valid skill found.');
            }

            foreach ($characters as $character) {
                foreach ($skills as $key=>$skill) {
                    if ($this->creditSkill($staff, $character, $skill, $data['quantities'][$key], 'Staff Grant', isset($data['notes']) ? $data['notes'] + ' - ' : null)) {
                        if ($character->user) {
                            Notifications::create('SKILL_GRANT', $character->user, [
                                'skill_name'     => $skill->name,
                                'skill_quantity' => $data['quantities'][$key],
                                'sender_url'     => $staff->url,
                                'sender_name'    => $staff->name,
                                'url'            => $character->url.'/skill-logs',
                            ]);
                        }
                    } else {
                        throw new \Exception('Failed to credit skills to '.$character->fullname.'.');
                    }
                }
            }

            return $this->commitReturn(true);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }

    /**
     * Credits skill to a character.
     *
     * @param Character $recipient
     * @param string    $type
     * @param array     $data
     * @param Skill     $skill
     * @param int       $quantity
     * @param mixed     $sender
     *
     * @return bool
     */
    public function creditSkill($sender, $recipient, $skill, $quantity, $type, $data = null) {
        DB::beginTransaction();

        try {
            // check that the character is the right species
            $species_match = false;
            foreach ($skill->species as $species) {
                if ($species->species_id == $recipient->image->species_id || ($species->is_subtype && $species->species_id == $recipient->image->subtype_id)) {
                    $species_match = true;
                    break;
                }
            }
            if (count($skill->species) && !$species_match) {
                throw new \Exception("This skill is not available to this character's species and/or subtype.");
            }

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
