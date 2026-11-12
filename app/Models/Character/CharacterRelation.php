<?php

namespace App\Models\Character;

use App\Models\Model;

class CharacterRelation extends Model {
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'character_1_id', 'character_2_id', 'info', 'parsed_info', 'type', 'status', 'is_visible',
        'character_1_accepted', 'character_2_accepted',
    ];

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'character_relations';

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'info'        => 'array',
        'parsed_info' => 'array',
        'type'        => 'array',
        'is_visible'  => 'array',
    ];

    /**
     * Whether the model contains timestamps to be saved and updated.
     *
     * @var string
     */
    public $timestamps = true;

    /**********************************************************************************************

        RELATIONS

    **********************************************************************************************/

    /**
     * Get first character.
     */
    public function characterOne() {
        return $this->belongsTo(Character::class, 'character_1_id');
    }

    /**
     * Get second character.
     */
    public function characterTwo() {
        return $this->belongsTo(Character::class, 'character_2_id');
    }

    /**********************************************************************************************

        OTHER FUNCTIONS

    **********************************************************************************************/

    /**
     * Returns the character in the relation based on the given character id.
     *
     * @param mixed $id
     */
    public function getCharacter($id) {
        return $this->character_1_id == $id ? $this->characterOne : $this->characterTwo;
    }

    /**
     * Returns the other character in the relation based on the given character id.
     *
     * @param mixed $id
     */
    public function getOtherCharacter($id) {
        return $this->character_1_id == $id ? $this->characterTwo : $this->characterOne;
    }

    /**
     * Gets the information for the relation based on the given character id.
     *
     * @param mixed $id
     */
    public function getRelationshipType($id) {
        return $this->type ? $this->type[$id == $this->character_1_id ? 0 : 1] : null;
    }

    /**
     * Gets the information for the relation based on the given character id.
     *
     * @param mixed $id
     */
    public function getRelationshipInfo($id) {
        return $this->info ? $this->info[$id == $this->character_1_id ? 0 : 1] : null;
    }

    /**
     * Gets the information for the relation based on the given character id.
     *
     * @param mixed $id
     */
    public function getRelationshipParsedInfo($id) {
        return $this->parsed_info ? $this->parsed_info[$id == $this->character_1_id ? 0 : 1] : null;
    }

    /**
     * Gets the visibility for the relation based on the given character id.
     *
     * @param mixed $id
     */
    public function getRelationshipVisibility($id) {
        return $this->is_visible ? $this->is_visible[$id == $this->character_1_id ? 0 : 1] : null;
    }

    /**
     * Get the character in the relation belonging to the given user id, if any.
     *
     * @param mixed $id
     */
    public function getCharacterForUser($id) {
        return $this->characterOne->user_id == $id ? $this->characterOne : ($this->characterTwo->user_id == $id ? $this->characterTwo : null);
    }

    /**
     * Get the character in the relation belonging to opposite of the given user id.
     *
     * @param mixed $id
     */
    public function getOtherCharacterForUser($id) {
        return $this->characterOne->user_id == $id ? $this->characterTwo : ($this->characterTwo->user_id == $id ? $this->characterOne : null);
    }

    /**
     * Get the other user in a relationship, given the user id.
     *
     * @param mixed $id
     */
    public function getOtherUser($id) {
        return $this->characterOne->user_id == $id ? $this->characterTwo->user : ($this->characterTwo->user_id == $id ? $this->characterOne->user : null);
    }
}
