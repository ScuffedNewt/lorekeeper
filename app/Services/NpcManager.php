<?php

namespace App\Services;

use App\Models\User\User;
use App\Models\User\UserNpcAffection;
use App\Models\Character\Character;
use Illuminate\Support\Facades\DB;

class NpcManager extends Service {

    /**********************************************************************************************
     * 
     * CHARACTER METHODS
     * 
     **********************************************************************************************/

    /**
     * Updates a character's NPC status.
     *
     * @param Character $character
     * @param array $data
     */
    public function editCharacterNpcInformation(Character $character, array $data) {
        DB::beginTransaction();

        try {

            if (!isset($data['is_npc'])) {
                $character->npcInformation()->delete();
                $character->is_npc = false;
                $character->save();
            } else {
                $character->is_npc = true;
                $character->save();

                $npcInformation = $character->npcInformation;
                if ($npcInformation) {
                    $npcInformation->update($data);
                } else {
                    $npcInformation = $character->npcInformation()->create($data);
                }
            }

            return $this->commitReturn(true);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }

    /**********************************************************************************************
     * 
     * USER METHODS
     * 
     **********************************************************************************************/

    /**
     * Updates a NPC's affection for a user.
     * 
     * @param Character $character
     * @param User $user
     * @param int $quantity
     */
    public function updateAffection(Character $character, User $user, int $quantity) {
        DB::beginTransaction();

        try {

            $affection = $user->npcAffection()->where('character_id', $character->id)->first();

            if ($affection) {
                $affection->affection += $quantity;
                $affection->save();
            } else {
                $user->npcAffection()->create([
                    'character_id' => $character->id,
                    'affection' => $quantity,
                ]);
            }

            return $this->commitReturn(true);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }

}
