<?php

namespace App\Models\Level;

use App\Models\Model;
use App\Models\Reward\Reward;

class Level extends Model {
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'previous_level_id', 'exp_required', 'level_type', 'description', 'parsed_description',
    ];

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'levels';

    /**
     * Validation rules for creation.
     *
     * @var array
     */
    public static $createRules = [
        'name'  => 'required',
    ];

    /**
     * Validation rules for updating.
     *
     * @var array
     */
    public static $updateRules = [
        'name'  => 'required',
    ];

    /**********************************************************************************************

        SCOPES

    **********************************************************************************************/

    /**
     * Orders levels by progression.
     *
     * @param mixed $query
     * @param mixed $type
     */
    public function scopeOrdered($query, $type = 'Character') {
        $levels = $query->where('level_type', $type)->get();
        $byId = $levels->keyBy('id');

        $prevIds = $levels->pluck('previous_level_id')->filter();
        $tail = $levels->firstWhere(fn ($lvl) => !$prevIds->contains($lvl->id));

        $orderedDesc = collect();
        while ($tail) {
            $orderedDesc->push($tail);
            $tail = $byId->get($tail->previous_level_id);
        }

        return $orderedDesc->reverse()->values();
    }

    /**********************************************************************************************

        RELATIONS

    **********************************************************************************************/

    /**
     * Get the rewards attached to this level.
     */
    public function rewards() {
        return $this->morphMany(Reward::class, 'object', 'object_model', 'object_id');
    }

    /**
     * Get the previous level.
     */
    public function previousLevel() {
        return $this->belongsTo(self::class, 'previous_level_id');
    }

    /**
     * Get the next level.
     */
    public function nextLevel() {
        return $this->hasOne(self::class, 'previous_level_id', 'id');
    }

    /**********************************************************************************************

        ATTRIBUTES

    **********************************************************************************************/

    /**
     * Displays the model's name, linked to its encyclopedia page.
     *
     * @return string
     */
    public function getDisplayNameAttribute() {
        return '<a href="'.url('world/levels').'/'.strtolower($this->level_type).'" class="display-prompt">'.ucfirst($this->level_type).' '.$this->name.'</a>';
    }

    /**
     * Gets the admin edit URL.
     *
     * @return string
     */
    public function getAdminUrlAttribute() {
        return url('admin/levels/'.strtolower($this->level_type).'/edit/'.$this->id);
    }

    /**
     * Gets the power required to edit this model.
     *
     * @return string
     */
    public function getAdminPowerAttribute() {
        return 'edit_claymores';
    }

    /**
     * Gets the currency's asset type for asset management.
     *
     * @return string
     */
    public function getAssetTypeAttribute() {
        return 'levels';
    }

    /**********************************************************************************************

        OTHER FUNCTIONS

    **********************************************************************************************/

    /**
     * True if $this is the same as $required, or is after it in the chain.
     *
     * @param Level $required
     */
    public function meetsOrExceeds($required) {
        if ($this->id == $required->id) {
            return true;
        }

        $visited = [];
        $node = $this;
        while ($node && $node->previous_level_id) {
            if (isset($visited[$node->id])) {
                return false;
            }
            $visited[$node->id] = true;
            $node = $node->previousLevel;

            if ($node && $node->id == $required->id) {
                return true;
            }
        }

        return false;
    }
}
