<?php

namespace App\Models\Daily;

use App\Models\Model;

class Daily extends Model {
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'sort', 'has_image', 'has_button_image', 'description', 'parsed_description', 'is_active', 'is_progressable', 'is_timed_daily', 'start_at', 'end_at', 'daily_timeframe',
        'progress_display', 'is_loop', 'is_streak', 'type', 'fee', 'currency_id',
    ];

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'daily';

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

        ACCESSORS

    **********************************************************************************************/

    /**
     * Get the rewards attached to this daily.
     */
    public function rewards() {
        return $this->hasMany('App\Models\Daily\DailyReward', 'daily_id');
    }

    /**
     * Get the timers attached to this daily.
     */
    public function timers() {
        return $this->hasMany('App\Models\Daily\DailyTimer', 'daily_id');
    }

    /**
     * Get wheel (if it exists).
     */
    public function wheel() {
        return $this->hasOne('App\Models\Daily\DailyWheel', 'daily_id');
    }

    /**
     * Get currency (if it exists).
     */
    public function currency() {
        return $this->belongsTo('App\Models\Currency\Currency');
    }

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
