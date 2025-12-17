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
     * Get the object that is currently in the plot.
     */
    public function getObjectAttribute() {
        if (!isset($this->data['object_id'])) {
            return null;
        }

        return GardenObject::find($this->data['object_id']);
    }

    /**
     * Gets the interacted count.
     */
    public function getInteractedCountAttribute() {
        return $this->data['interacted_count'] ?? 0;
    }

    /**
     * Returns the actions required to be ready.
     */
    public function getActionsRequiredAttribute() {
        return $this->object->data['interactions'] ?? 1;
    }

    /**
     * gets whether the plot is ready to be waterred.
     */
    public function getActionReadyAttribute() {
        if (!$this->object || !$this->started_at) {
            return false;
        }

        if (!isset($this->data['interacted_at'])) {
            return true;
        }

        return ($this->timeUntilReady < Carbon::now() && $this->interactedCount < $this->actionsRequired) || $this->interactedCount >= $this->actionsRequired;
    }

    /**
     * Returns whether or not the plot is ready to be collected.
     */
    public function getCanCollectAttribute() {
        return $this->actionReady && $this->interactedCount >= $this->actionsRequired && $this->timeUntilReady < Carbon::now();
    }

    /**
     * Returns the carbon instance of the next action ready time.
     */
    public function getTimeUntilReadyAttribute() {
        $growthTime = $this->object->data['growth_time'] ?? 24;

        if (!isset($this->data['interacted_at'])) {
            return $this->started_at->addHours($growthTime);
        }

        $interactedAt = Carbon::parse($this->data['interacted_at']);

        return $interactedAt->addHours($growthTime);
    }
}
