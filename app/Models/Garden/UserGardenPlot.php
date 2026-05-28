<?php

namespace App\Models\Garden;

use App\Models\Model;
use App\Models\User\User;
use Carbon\Carbon;

class UserGardenPlot extends Model {
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'plot_id', 'user_id', 'started_at', 'data',
    ];

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'user_garden_plots';

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'data'       => 'array',
        'started_at' => 'datetime',
    ];

    /**
     * Whether the model contains timestamps to be saved and updated.
     *
     * @var string
     */
    public $timestamps = false;

    /**********************************************************************************************

        RELATIONS

    **********************************************************************************************/

    /**
     * Get the user who owns the stack.
     */
    public function user() {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Get the garden plot associated with this user plot.
     */
    public function plot() {
        return $this->belongsTo(GardenPlot::class, 'plot_id');
    }

    /**
     * Get the garden that this plot is in by using the plot relation.
     */
    public function garden() {
        return $this->plot->garden();
    }

    /**********************************************************************************************

        ATTRIBUTES

    **********************************************************************************************/

    /**
     * Return the modifiers from the data attribute as an array.
     */
    public function getModifiersAttribute() {
        return $this->data['modifiers'] ?? [];
    }

    /**
     * Get the object currently in the plot.
     */
    public function getObjectAttribute() {
        if (!isset($this->data['object_id'])) {
            return null;
        }

        return GardenObject::find($this->data['object_id']);
    }

    /**
     * Number of interactions performed.
     */
    public function getInteractedCountAttribute() {
        return $this->data['interacted_count'] ?? 0;
    }

    /**
     * Total interactions required.
     */
    public function getActionsRequiredAttribute() {
        return $this->object?->data['interactions'] ?? 1;
    }

    /**
     * Whether the next interaction can be performed.
     */
    public function getActionReadyAttribute() {
        if (!$this->object || !$this->started_at) {
            return false;
        }

        if ($this->isPerished) {
            return false;
        }

        if ($this->interactedCount >= $this->actionsRequired) {
            return false;
        }

        return $this->timeUntilReady <= Carbon::now();
    }

    /**
     * Whether the object can be collected.
     */
    public function getCanCollectAttribute() {
        if (!$this->object || !$this->started_at) {
            return false;
        }

        if ($this->isPerished) {
            return false;
        }

        return $this->interactedCount >= $this->actionsRequired
            && $this->timeUntilReady <= Carbon::now();
    }

    /**
     * When the next interaction or collection becomes available.
     */
    public function getTimeUntilReadyAttribute() {
        if (!$this->object || !$this->started_at) {
            return null;
        }

        $growthTime = $this->growthTime;

        $baseTime = isset($this->data['interacted_at'])
            ? Carbon::parse($this->data['interacted_at'])
            : $this->started_at;

        return $baseTime->addHours($growthTime);
    }

    /**
     * Growth time after any pre-placement fertiliser reduction.
     *
     * @return float
     */
    public function getGrowthTimeAttribute() {
        $growthTime = (float) ($this->object?->data['growth_time'] ?? 24);
        $fertiliserId = $this->modifiers['fertiliser'] ?? null;
        $fertiliser = $fertiliserId ? GardenObject::find($fertiliserId) : null;
        if (!$fertiliser) {
            return $growthTime;
        }

        $reduction = (float) ($fertiliser->data['time_reduced'] ?? 0);
        if (($fertiliser->data['formula'] ?? 0) && ($this->object?->requiredQuality ?? 0) > 0) {
            $reduction *= ($fertiliser->data['quality'] ?? 0) / $this->object->requiredQuality;
        }

        return max(0, $growthTime - $reduction);
    }

    /**
     * Whether this plot's current seed is allowed to perish.
     *
     * @return bool
     */
    public function getCanPerishAttribute() {
        return $this->object?->type === 'seed'
            && (($this->plot?->garden?->can_perish ?? false) || ($this->plot?->data['can_perish'] ?? false));
    }

    /**
     * When the current seed perishes if it is not tended to or collected.
     *
     * @return \Carbon\Carbon|null
     */
    public function getPerishesAtAttribute() {
        if (!$this->canPerish || !$this->timeUntilReady) {
            return null;
        }

        return $this->timeUntilReady->copy()->addHours($this->object->data['perish_time'] ?? 24);
    }

    /**
     * Whether the current seed has passed its maintenance grace period.
     *
     * @return bool
     */
    public function getIsPerishedAttribute() {
        return $this->perishesAt?->isPast() ?? false;
    }
}
