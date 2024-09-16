<?php

namespace App\Models\User;

use App\Models\Character\Character;
use App\Models\Model;
use App\Models\Pet\Pet;
use App\Models\Pet\PetDrop;
use App\Models\Pet\PetEvolution;
use App\Models\Pet\PetVariant;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\SoftDeletes;

class UserPet extends Model {
    use SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'data', 'pet_id', 'user_id', 'attached_at', 'pet_name', 'has_image', 'artist_url', 'artist_id', 'description',
        'evolution_id', 'variant_id', 'sort', 'bonded_at',
    ];

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'user_pets';

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'bonded_at' => 'datetime',
    ];

    /**
     * Whether the model contains timestamps to be saved and updated.
     *
     * @var string
     */
    public $timestamps = true;

    /**********************************************************************************************

        RELATIONS

    **********************************************************************************************/

    /**
     * Get the user who owns the stack.
     */
    public function user() {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the pet associated with this pet stack.
     */
    public function pet() {
        return $this->belongsTo(Pet::class);
    }

    /**
     * Get the character associated with this pet stack.
     */
    public function character() {
        return $this->belongsTo(Character::class, 'character_id');
    }

    /**
     * Get the variant associated with this pet stack.
     */
    public function variant() {
        return $this->belongsTo(PetVariant::class, 'variant_id');
    }

    /**
     * Get the evolution associated with this pet stack.
     */
    public function evolution() {
        return $this->belongsTo(PetEvolution::class, 'evolution_id');
    }

    /**
     * Get the pet's pet drop data.
     */
    public function drops() {
        if (!$this->pet->dropData) {
            return $this->belongsTo('App\Models\Loot\Loot', 'rewardable_id', 'loot_table_id')->whereNull('loot_table_id');
        }
        if (!PetDrop::where('user_pet_id', $this->id)->first()) {
            PetDrop::create([
                'drop_id'         => $this->pet->dropData->id,
                'user_pet_id'     => $this->id,
                'parameters'      => $this->pet->dropData->rollParameters(),
                'drops_available' => 0,
                'next_day'        => Carbon::now()
                    ->add($this->pet->dropData->frequency, $this->pet->dropData->interval)
                    ->startOf($this->pet->dropData->interval),
            ]);
            // if we delete old drop data, populate with new
        } elseif (!PetDrop::where('user_pet_id', $this->id)->where('drop_id', $this->pet->dropData->id)->first()) {
            PetDrop::where('user_pet_id', $this->id)->delete();
            PetDrop::create([
                'drop_id'         => $this->pet->dropData->id,
                'user_pet_id'     => $this->id,
                'parameters'      => $this->pet->dropData->rollParameters(),
                'drops_available' => 0,
                'next_day'        => Carbon::now()
                    ->add($this->pet->dropData->frequency, $this->pet->dropData->interval)
                    ->startOf($this->pet->dropData->interval),
            ]);
        }

        return $this->hasOne(PetDrop::class, 'user_pet_id');
    }

    /**
     * Get the user that drew the pet art.
     */
    public function artist() {
        return $this->belongsTo(User::class, 'artist_id');
    }

    /**
     * Get the pets current level.
     */
    public function level() {
        return $this->hasOne(UserPetLevel::class, 'user_pet_id');
    }

    /**********************************************************************************************

        ACCESSORS

    **********************************************************************************************/

    /**
     * Get the data attribute as an associative array.
     *
     * @return array
     */
    public function getDataAttribute() {
        return json_decode($this->attributes['data'], true);
    }

    /**
     * Checks if the stack is transferrable.
     *
     * @return array
     */
    public function getIsTransferrableAttribute() {
        if (!isset($this->data['disallow_transfer']) && $this->pet->allow_transfer) {
            return true;
        }

        return false;
    }

    /**
     * Gets the stack's asset type for asset management.
     *
     * @return string
     */
    public function getAssetTypeAttribute() {
        return 'user_pets';
    }

    /**
     * Gets the file directory containing the model's image.
     *
     * @return string
     */
    public function getImageDirectoryAttribute() {
        return 'images/data/user-pets';
    }

    /**
     * Gets the file name of the model's image.
     *
     * @return string
     */
    public function getImageFileNameAttribute() {
        return $this->id.'-image.png';
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
     * Gets the URL of the model's image.
     *
     * @return string
     */
    public function getImageUrlAttribute() {
        if (!$this->has_image) {
            return null;
        }

        return asset($this->imageDirectory.'/'.$this->imageFileName);
    }

    /**
     * Get the artist of the item's image.
     *
     * @return string
     */
    public function getPetArtistAttribute() {
        if (!$this->artist_url && !$this->artist_id) {
            return null;
        }

        // Check to see if the artist exists on site
        $artist = checkAlias($this->artist_url, false);
        if (is_object($artist)) {
            $this->artist_id = $artist->id;
            $this->artist_url = null;
            $this->save();
        }

        if ($this->artist_id) {
            return $this->artist->displayName;
        } elseif ($this->artist_url) {
            return prettyProfileLink($this->artist_url);
        }
    }

    /**********************************************************************************************

        OTHER FUNCTIONS

    **********************************************************************************************/

    /**
     * Get the url for the pet's custom page.
     *
     * @param mixed|null $id
     *
     * @return array
     */
    public function pageUrl($id = null) {
        if ($id && $this->user_id == $id) {
            return url('pets/view/'.$this->id);
        }

        return url('user/'.$this->user->name.'/pets/'.$this->id);
    }

    /**
     * gets all drops this pet is eligible for.
     */
    public function getAvailableDropsAttribute() {
        if (!$this->pet->dropData) {
            return null;
        }
        $rewards = [];
        // otherwise return base rewards + variant rewards
        if ($this->variant_id) {
            if ($this->variant->dropData) {
                $rewards[] = $this->variant->dropData;
            }
        }
        if (!$this->pet->dropData->override) {
            $rewards[] = $this->pet->dropData;
        }

        return $rewards;
    }

    /**
     * Determines if the user can bond with the pet.
     *
     * @param mixed $reason
     */
    public function canBond($reason = false) {
        // create level if needed
        if (!$this->level) {
            $this->level()->create([
                'bonding_level'       => 0,
                'bonding'             => 0,
            ]);
            $this->refresh();
            $this->level->refresh();
        }

        if ($this->bonded_at) {
            // check if its the next day
            if ($this->bonded_at->isToday()) {
                return $reason ? 'You have already bonded with this pet today.' : false;
            }
        }
        if (!$this->level->nextLevel) {
            return $reason ? 'This pet is already at its maximum level.' : false;
        }

        return true;
    }
}
