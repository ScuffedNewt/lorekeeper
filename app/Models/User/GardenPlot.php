<?php

namespace App\Models\User;

use App\Models\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class GardenPlot extends Model
{

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'currency_id', 'free', 'plot_cost', 'plot_type'
    ];

    /**
     * Whether the model contains timestamps to be saved and updated.
     *
     * @var string
     */
    public $timestamps = false;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'garden_plots';

    /**********************************************************************************************
    
        RELATIONS

    **********************************************************************************************/

    /**
     * Get the user who owns the stack.
     */
    public function currency() 
    {
        return $this->belongsTo('App\Models\Currency\Currency');
    }

    /**********************************************************************************************
    
        ATTRIBUTES

    **********************************************************************************************/

    /**
     * Returns the panel title for the plot type
     */
    public function getPanelTitleAttribute()
    {
        switch($this->plot_type) {
            case 'Coop':
                return 'Coop Nests';
            case 'Barn':
                return 'Barn Stables';
            case 'Garden':
                return 'Plots';
            default:
                return 'Plots';
        }
    }

    /**
     * Gets the background image for the plot type
     */
    public function getBackgroundImageAttribute()
    {
        switch($this->plot_type) {
            case 'Coop':
                return 'images/garden/croop_bg.png';
            case 'Barn':
                return 'images/garden/barn_bg.png';
            case 'Garden':
                return 'images/garden/crops_bg.png';
            default:
                return 'images/garden/crops_bg.png';
        }
    }

    /**
     * Returns the item titles for those that can be placed in the plot
     */
    public function getItemTitleAttribute()
    {
        switch($this->plot_type) {
            case 'Coop':
                return 'Coop Store';
            case 'Barn':
                return 'Barn Pastures';
            case 'Garden':
                return 'Seeds';
            default:
                return 'Seeds';
        }
    }
}
