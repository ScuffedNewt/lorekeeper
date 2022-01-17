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
        'plot_id', 'user_id', 'item_id', 'started_at', 'water_at'
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
        return $this->belongsTo('App\Models\User\GardenPlot', 'plot_id');
    }

    /**
     * Get the item associated with this item stack.
     */
    public function item() 
    {
        return $this->belongsTo('App\Models\Item\Item');
    }

    /**********************************************************************************************
    
        ATTRIBUTES

    **********************************************************************************************/

    /**
     * Gets whether the plot can currently be watered / fed.
     */
    public function getNotWateringTimeAttribute()
    {
        return $this->water_at > Carbon::now() && $this->waterings < $this->item->tags->where('tag', strtolower($this->plot->plot_type))
        ->first()->data[($this->plot->plot_type == 'Seed' ? 'waterings' : 'feedings')];
    }

    /**
     * returns whether or not the plot is ready to be claimed
     */
    public function getReadyToClaimAttribute()
    {
        return !$this->notWateringTime && $this->waterings >= $this->item->tags->where('tag', strtolower($this->plot->plot_type))
        ->first()->data[($this->plot->plot_type == 'Seed' ? 'waterings' : 'feedings')];
    }

    /**
     * Gives the display message for the item currently held in the plot
     */
    public function getDisplayHoldingAttribute()
    {
        switch($this->plot->plot_type)
        {
            case 'Coop':
            case 'Barn':
                return 'Currently Housing '. $this->item->name;
                break;
            case 'Seed':
                return 'Growing ' . $this->item->name . ' seeds';
                break;
        }
    }
}
