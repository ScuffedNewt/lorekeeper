<?php

namespace App\Models\User;

use App\Models\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

use Carbon\Carbon;
class UserGardenPlot extends Model
{

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'plot_id', 'user_id', 'item_id', 'started_at', 'water_at', 'is_dead', 'waterings', 'time', 'modifiers'
    ];

    /**
     * Whether the model contains timestamps to be saved and updated.
     *
     * @var string
     */
    public $timestamps = false;

    protected $dates = ['started_at', 'water_at'];
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'user_garden_plots';

    /**********************************************************************************************

        RELATIONS

    **********************************************************************************************/

    /**
     * Get the user who owns the stack.
     */
    public function user()
    {
        return $this->belongsTo('App\Models\User\User');
    }

    /**
     * Get the item associated with this item stack.
     */
    public function plot()
    {
        return $this->belongsTo('App\Models\GardenPlot', 'plot_id');
    }

    /**
     * Get the item associated with this item stack.
     */
    public function item()
    {
        return $this->belongsTo(getAssetModelString(config('lorekeeper.garden.'.$this->plot->plot_type.'.type')));
    }

    /**********************************************************************************************

        ATTRIBUTES

    **********************************************************************************************/

    /**
     * Returns total amount of actions required to complete plot
     */
    public function getActionsRequiredAttribute()
    {
        if (!$this->item) return 0;
        if (config('lorekeeper.garden.'.$this->plot->plot_type.'.type') == 'item') {
            return $this->item->tags->where('tag', $this->plot->plot_type)->first()->data['actions'];
        }
        return 0;
    }

    /**
     * gets whether the plot is ready to be waterred
     */
    public function getActionReadyAttribute()
    {
        return ($this->water_at < Carbon::now() && $this->waterings < $this->actionsRequired) || $this->waterings >= $this->actionsRequired;
    }

    /**
     * returns whether or not the plot is ready to be claimed
     */
    public function getFinishReadyAttribute()
    {
        return $this->actionReady && $this->waterings >= $this->actionsRequired;
    }

    /**
     * Gives the display message for the item currently held in the plot
     */
    public function getDisplayHoldingAttribute()
    {
        switch($this->plot->plot_type)
        {
            case 'coop': case 'barn':
                return 'Currently Housing '. $this->item->name;
                break;
            case 'seed':
                return 'Growing ' . $this->item->name . ' seeds';
                break;
        }
    }

    /**
     * returns string for use in watering tooltip so calc not done in blade
     */
    public function getActionTermAttribute()
    {
        return $this->actionWord . 'ing' . ($this->waterings > 1 ? 's' : '');
    }

    /**
     * gets the action word from config
     */
    public function getActionWordAttribute()
    {
        return config('lorekeeper.garden.' . strtolower($this->plot->plot_type) . '.action_word');
    }

    /**
     * gets the favicon for the care type
     */
    public function getActionIconAttribute()
    {
        return '<i class="' . config('lorekeeper.garden.' . strtolower($this->plot->plot_type) . '.action_icon') . '"></i>';
    }

    /**
     * Gets the put-together sentence with action word and icon
     */
    public function getActionSentenceAttribute()
    {
        return $this->actionWord . ' ' . $this->actionIcon;
    }

    /**
     * gets the finish sentence from config
     */
    public function getFinishSentenceAttribute()
    {
        return '<i class="mr-2 '.config('lorekeeper.garden.' . strtolower($this->plot->plot_type) . '.finish_icon').'"></i> ' .
                config('lorekeeper.garden.' . strtolower($this->plot->plot_type) . '.finish_word') . ' ' .
                '<i class="ml-2 '.config('lorekeeper.garden.' . strtolower($this->plot->plot_type) . '.finish_icon').'"></i> ';
    }
}
