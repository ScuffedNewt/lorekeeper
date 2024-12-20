<?php

namespace App\Models\Daily;

use App\Models\Model;

class DailyWheel extends Model {
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'daily_id', 'wheel_extension', 'background_extension', 'stopper_extension', 'size', 'alignment', 'segment_number', 'segment_style', 'text_orientation', 'text_fontsize',
    ];

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'daily_wheels';

    /**
     * Validation rules for creation.
     *
     * @var array
     */
    public static $createRules = [
        'daily_id'             => 'required',
        'wheel_extension'      => 'mimes:png',
        'background_extension' => 'mimes:png,jpg',
        'stopper_extension'    => 'mimes:png',
    ];

    /**
     * Validation rules for updating.
     *
     * @var array
     */
    public static $updateRules = [
        'daily_id'             => 'required',
        'wheel_extension'      => 'mimes:png',
        'background_extension' => 'mimes:png,jpg',
        'stopper_extension'    => 'mimes:png',
    ];

    /**********************************************************************************************

        ACCESSORS

     **********************************************************************************************/

    /**
     * Gets the file directory containing the model's image.
     *
     * @return string
     */
    public function getImageDirectoryAttribute() {
        return 'images/data/dailies/wheels';
    }

    /**
     * Gets the path to the file directory containing the model's image.
     *
     * @return string
     */
    public function getImagePathAttribute() {
        return public_path($this->imageDirectory);
    }

    /**
     * Gets the file name of the model's wheel.
     *
     * @return string
     */
    public function getWheelFileNameAttribute() {
        return $this->id.'-wheel_image.'.$this->wheel_extension;
    }

    /**
     * Gets the file name of the model's stopper.
     *
     * @return string
     */
    public function getStopperFileNameAttribute() {
        return $this->id.'-stopper_image.'.$this->stopper_extension;
    }

    /**
     * Gets the file name of the model's background.
     *
     * @return string
     */
    public function getBackgroundFileNameAttribute() {
        return $this->id.'-background_image.'.$this->background_extension;
    }

    /**
     * Gets the URL of the model's wheel image.
     *
     * @return string
     */
    public function getWheelUrlAttribute() {
        if (!$this->wheel_extension) {
            return null;
        }

        return asset($this->imageDirectory.'/'.$this->wheelFileName);
    }

    /**
     * Gets the URL of the model's stopper image.
     *
     * @return string
     */
    public function getStopperUrlAttribute() {
        if (!$this->stopper_extension) {
            return '/images/stopper.png';
        }

        return asset($this->imageDirectory.'/'.$this->stopperFileName);
    }

    /**
     * Gets the URL of the model's background image.
     *
     * @return string
     */
    public function getBackgroundUrlAttribute() {
        if (!$this->background_extension) {
            return null;
        }

        return asset($this->imageDirectory.'/'.$this->backgroundFileName);
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
     * Get the segment_style as an associative array.
     *
     * @return array
     */
    public function getSegmentStylesAttribute() {
        return json_decode($this->attributes['segment_style'], true);
    }

    /**
     * Get the segment_style as an associative array.
     *
     * @return array
     */
    public function getSegmentStyleReplaceAttribute() {
        return str_replace('"', "'", $this->attributes['segment_style']);
    }

    /**********************************************************************************************

        OTHER FUNCTIONS

     **********************************************************************************************/

    public function marginAlignment() {
        switch ($this->alignment) {
            case 'left':
                return 'mr-auto ml-lg-5 ml-0';
                break;
            case 'right':
                return 'ml-auto mr-lg-5 mr-0';
                break;
            case 'center':
                return 'm-auto';
                break;
        }

        return 'm-auto';
    }
}
