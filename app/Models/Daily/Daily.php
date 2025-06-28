<?php

namespace App\Models\Daily;

use App\Models\Model;
use Carbon\Carbon;

class Daily extends Model {
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'sort', 'has_image', 'description', 'parsed_description', 'prize_display', 'is_active', 'start_at', 'end_at', 'daily_timeframe',
        'type', 'data',
    ];

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'dailies';

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'data'     => 'array',
        'start_at' => 'datetime',
        'end_at'   => 'datetime',
    ];

    /**
     * Validation rules for creation.
     *
     * @var array
     */
    public static $createRules = [
        'name'        => 'required|unique:item_categories|between:3,100',
        'description' => 'nullable',
        'image'       => 'mimes:png',
    ];

    /**
     * Validation rules for updating.
     *
     * @var array
     */
    public static $updateRules = [
        'name'        => 'required|between:3,100',
        'description' => 'nullable',
        'image'       => 'mimes:png',
    ];

    /**********************************************************************************************

        RELATIONSHIPS

    **********************************************************************************************/

    /**
     * Get the rewards attached to this daily.
     */
    public function rewards() {
        return $this->hasMany(DailyReward::class, 'daily_id')->orderBy('step', 'ASC');
    }

    /**
     * Get the timers attached to this daily.
     */
    public function timers() {
        return $this->hasMany(DailyTimer::class, 'daily_id');
    }

    /**
     * Get wheel (if it exists).
     */
    public function wheel() {
        return $this->hasOne(DailyWheel::class, 'daily_id');
    }

    /**********************************************************************************************

        SCOPES

    **********************************************************************************************/

    /**
     * Scope a query to only include active dailies.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActive($query) {
        return $query->where('is_active', 1)
            ->where(function ($query) {
                $query->whereNull('start_at')->orWhere('start_at', '<', Carbon::now());
            })->where(function ($query) {
                $query->whereNull('end_at')->orWhere('end_at', '>', Carbon::now());
            });
    }

    /**********************************************************************************************

        ATTRIBUTES

    **********************************************************************************************/

    /**
     * Displays the daily's name, linked to its purchase page.
     *
     * @return string
     */
    public function getDisplayNameAttribute() {
        return '<a href="'.$this->url.'" class="display-shop">'.$this->name.'</a>';
    }

    /**
     * Gets the file directory containing the model's image.
     *
     * @return string
     */
    public function getImageDirectoryAttribute() {
        return 'images/data/dailies';
    }

    /**
     * Gets the file name of the model's image.
     *
     * @return string
     */
    public function getDailyImageFileNameAttribute() {
        return $this->id.'-image.png';
    }

    /**
     * Gets the file name of the model's image.
     *
     * @return string
     */
    public function getButtonImageFileNameAttribute() {
        return $this->id.'-button-image.png';
    }

    /**
     * Gets the path to the file directory containing the model's image.
     *
     * @return string
     */
    public function getDailyImagePathAttribute() {
        return public_path($this->imageDirectory);
    }

    /**
     * Gets the URL of the model's image.
     *
     * @return string
     */
    public function getDailyImageUrlAttribute() {
        if (!$this->has_image) {
            return null;
        }

        return asset($this->imageDirectory.'/'.$this->dailyImageFileName);
    }

    /**
     * Gets the URL of the model's button image.
     *
     * @return string
     */
    public function getButtonImageUrlAttribute() {
        if (!$this->has_button_image) {
            return null;
        }

        return asset($this->imageDirectory.'/'.$this->buttonImageFileName);
    }

    /**
     * Gets the URL of the model's encyclopedia page.
     *
     * @return string
     */
    public function getUrlAttribute() {
        return url(__('dailies.dailies').'/'.$this->id);
    }

    /**
     * Gets the max step of the daily rewards.
     *
     * @return string
     */
    public function getMaxStepAttribute() {
        $max = $this->rewards()->get()->max(function ($reward) {
            return $reward->step;
        });

        return ($max > 0) ? $max : 1;
    }

    /**
     * Get the viewing URL of the daily.
     *
     * @return string
     */
    public function getViewUrlAttribute() {
        return url(__('dailies.dailies').'/'.$this->id);
    }

    /**
     * Returns the name of the step for a specific wheel type.
     */
    public function getStepTypeAttribute() {
        switch ($this->type) {
            case 'Wheel':
                return 'Segment';
            case 'Advent':
                return 'Day';
            default:
                return 'Step';
        }
    }

    /**********************************************************************************************

        OTHER FUNCTIONS

    **********************************************************************************************/

    /*
     * Gets the current date associated to the daily's timeframe
     */
    public function getDailyTimeframeDateAttribute() {
        switch ($this->daily_timeframe) {
            case 'yearly':
                $date = date('Y-m-d H:i:s', strtotime('January 1st'));
                break;
            case 'monthly':
                $date = date('Y-m-d H:i:s', strtotime('midnight first day of this month'));
                break;
            case 'weekly':
                $date = date('Y-m-d H:i:s', strtotime('last sunday'));
                break;
            case 'daily':
                $date = date('Y-m-d H:i:s', strtotime('midnight'));
                break;
            default:
                $date = null;
        }

        return $date;
    }

    /*
     * Gets the date associated with the next daily pickup.
     */
    public function getNextDateAttribute() {
        switch ($this->daily_timeframe) {
            case 'yearly':
                $date = date('Y-m-d H:i:s', strtotime('+1 Year', strtotime('January 1st')));
                break;
            case 'monthly':
                $date = date('Y-m-d H:i:s', strtotime('+1 Month', strtotime('midnight first day of this month')));
                break;
            case 'weekly':
                $date = date('Y-m-d H:i:s', strtotime('+1 Week +1 Day', strtotime('last sunday')));
                break;
            case 'daily':
                $date = date('Y-m-d H:i:s', strtotime('+1 Day', strtotime('midnight')));
                break;
            default:
                $date = null;
        }

        return $date;
    }
}
