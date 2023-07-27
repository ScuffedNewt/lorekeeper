<?php

namespace App\Models\User;

use App\Models\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class UserPet extends Model
{
    use SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'data', 'pet_id', 'user_id', 'attached_at', 'pet_name', 'has_image', 'artist_url', 'artist_id', 'description'
    ];

    /**
     * Whether the model contains timestamps to be saved and updated.
     *
     * @var string
     */
    public $timestamps = true;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'user_pets';

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
     * Get the pet associated with this pet stack.
     */
    public function pet() 
    {
        return $this->belongsTo('App\Models\Pet\Pet');
    }

    public function character()
    {
        return $this->belongsTo('App\Models\Character\Character', 'chara_id');
    }

    /**
     * Get the user that drew the pet art.
     */
    public function artist()
    {
        return $this->belongsTo('App\Models\User\User', 'artist_id');
    }

    /**********************************************************************************************
    
        ACCESSORS

    **********************************************************************************************/

    /**
     * Get the data attribute as an associative array.
     *
     * @return array
     */
    public function getDataAttribute() 
    {
        return json_decode($this->attributes['data'], true);
    }
    
    /**
     * Checks if the stack is transferrable.
     *
     * @return array
     */
    public function getIsTransferrableAttribute()
    {
        if(!isset($this->data['disallow_transfer']) && $this->pet->allow_transfer) return true;
        return false;
    }

    /**
     * Gets the stack's asset type for asset management.
     *
     * @return string
     */
    public function getAssetTypeAttribute()
    {
        return 'user_pets';
    }

        /**
     * Gets the file directory containing the model's image.
     *
     * @return string
     */
    public function getImageDirectoryAttribute()
    {
        return 'images/data/user-pets';
    }

    /**
     * Gets the file name of the model's image.
     *
     * @return string
     */
    public function getImageFileNameAttribute()
    {
        return $this->id . '-image.png';
    }

    /**
     * Gets the path to the file directory containing the model's image.
     *
     * @return string
     */
    public function getImagePathAttribute()
    {
        return public_path($this->imageDirectory);
    }

    /**
     * Gets the URL of the model's image.
     *
     * @return string
     */
    public function getImageUrlAttribute()
    {
        if (!$this->has_image) return null;
        return asset($this->imageDirectory . '/' . $this->imageFileName);
    }

    /**
     * Get the artist of the item's image.
     *
     * @return string
     */
    public function getPetArtistAttribute()
    {
        if(!$this->artist_url && !$this->artist_id) return null;

        // Check to see if the artist exists on site
        $artist = checkAlias($this->artist_url, false);
        if(is_object($artist)) {
            $this->artist_id = $artist->id;
            $this->artist_url = null;
            $this->save();
        }

        if($this->artist_id)
        {
            return $this->artist->displayName;
        }
        else if ($this->artist_url)
        {
            return prettyProfileLink($this->artist_url);
        }
    }
}
