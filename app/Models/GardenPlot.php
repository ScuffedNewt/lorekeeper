<?php

namespace App\Models;

class GardenPlot extends Model {
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'currency_id', 'free', 'plot_cost', 'plot_type',
    ];

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'garden_plots';

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
    public function currency() {
        return $this->belongsTo('App\Models\Currency\Currency');
    }

    /**********************************************************************************************

        ATTRIBUTES

    **********************************************************************************************/

    /**
     * Returns the panel title for the plot type.
     */
    public function getPanelTitleAttribute() {
        return config('lorekeeper.garden.plots.'.$this->plot_type.'.plot_title');
    }

    /**
     * Gets the background image for the plot type.
     */
    public function getBackgroundImageAttribute() {
        switch ($this->plot_type) {
            case 'coop':
                return 'images/garden/coop_bg.png';
            case 'barn':
                return 'images/garden/barn_bg.png';
            case 'seed':
                return 'images/garden/crops_bg.png';
            default:
                return 'images/garden/crops_bg.png';
        }
    }

    /**
     * Returns the item titles for those that can be placed in the plot.
     */
    public function getItemTitleAttribute() {
        switch ($this->plot_type) {
            case 'Coop':
                return 'Coop Store';
            case 'Barn':
                return 'Barn Pastures';
            case 'Seed':
                return 'Seeds';
            default:
                return 'Seeds';
        }
    }

    public function getIsFreeAttribute() {
        if ($this->free) {
            return true;
        }
        if (!$this->currency_id) {
            return true;
        }

        return false;
    }
}
