<?php

namespace App\Models\Recipe;

use App\Models\Model;
use App\Models\User\UserCurrency;
use Carbon\Carbon;

class Recipe extends Model {
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'has_image', 'needs_unlocking', 'description', 'parsed_description', 'reference_url', 'artist_alias', 'artist_url',
        'open_at', 'close_at', 'time', 'is_visible', 'required_slot_id', 'recipe_category_id',
    ];

    protected $appends = ['image_url'];

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'recipes';

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'output' => 'array',
    ];

    /**
     * Validation rules for creation.
     *
     * @var array
     */
    public static $createRules = [
        'name'        => 'required|unique:recipes',
        'description' => 'nullable',
        'image'       => 'mimes:png',
    ];

    /**
     * Validation rules for updating.
     *
     * @var array
     */
    public static $updateRules = [
        'name'        => 'required',
        'description' => 'nullable',
        'image'       => 'mimes:png',
    ];

    /**********************************************************************************************

        RELATIONS

    **********************************************************************************************/

    /**
     * Get the recipe's ingredients.
     */
    public function ingredients() {
        return $this->hasMany(RecipeIngredient::class, 'recipe_id');
    }

    /**
     * Get the users who have this recipe.
     */
    public function users() {
        return $this->belongsToMany(User::class, 'user_recipes')->withPivot('id');
    }

    /**
     * Gets the recipe's required slot type.
     */
    public function requiredSlot() {
        return $this->belongsTo(RecipeSlot::class, 'required_slot_id');
    }

    /**
     * Gets the recipe's category.
     */
    public function category() {
        return $this->belongsTo(RecipeCategory::class, 'recipe_category_id');
    }

    /**********************************************************************************************

        SCOPES

    **********************************************************************************************/

    /**
     * Scope a query to only include active prompts.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param mixed|null                            $user
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActive($query, $user = null) {
        if ($user && $user->hasPower('edit_data')) {
            return $query;
        }

        return $query->where('is_visible', 1)->where(function ($query) {
            $query->whereNull('open_at')->orWhere('open_at', '<', Carbon::now());
        })->where(function ($query) {
            $query->whereNull('close_at')->orWhere('close_at', '>', Carbon::now());
        });
    }

    /**
     * Scope a query to sort items in alphabetical order.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param bool                                  $reverse
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeSortAlphabetical($query, $reverse = false) {
        return $query->orderBy('name', $reverse ? 'DESC' : 'ASC');
    }

    /**
     * Scope a query to sort items by newest first.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeSortNewest($query) {
        return $query->orderBy('id', 'DESC');
    }

    /**
     * Scope a query to sort features oldest first.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeSortOldest($query) {
        return $query->orderBy('id');
    }

    /**
     * Scope a query to only show recipes that need to be unlocked.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeSortNeedsUnlocking($query) {
        return $query->where('needs_unlocking', 1);
    }

    /**
     * Scope a query to sort recipes in category order.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeSortCategory($query) {
        if (RecipeCategory::all()->count()) {
            return $query->orderBy(RecipeCategory::select('sort')->whereColumn('recipes.recipe_category_id', 'recipe_categories.id'), 'DESC');
        }

        return $query;
    }

    /**********************************************************************************************

        ACCESSORS

    **********************************************************************************************/

    /**
     * Gets the decoded output json.
     *
     * @return array
     */
    public function getRewardsAttribute() {
        $rewards = [];
        if ($this->output) {
            $assets = parseAssetData($this->output);
            foreach ($assets as $type => $a) {
                $class = getAssetModelString($type, false);
                foreach ($a as $id => $asset) {
                    $rewards[] = (object) [
                        'rewardable_type' => $class,
                        'rewardable_id'   => $id,
                        'quantity'        => $asset['quantity'],
                    ];
                }
            }
        }

        return $rewards;
    }

    /**
     * Gets the URL of the individual recipe's page, by ID.
     *
     * @return string
     */
    public function getIdUrlAttribute() {
        return url('world/recipes/'.$this->id);
    }

    /**
     * Displays the model's name, linked to its encyclopedia page.
     *
     * @return string
     */
    public function getDisplayNameAttribute() {
        return '<a href="'.$this->idUrl.'" class="display-item">'.$this->name.'</a>';
    }

    /**
     * Gets the file directory containing the model's image.
     *
     * @return string
     */
    public function getImageDirectoryAttribute() {
        return 'images/data/recipes';
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
     * Gets the URL of the model's encyclopedia page.
     *
     * @return string
     */
    public function getUrlAttribute() {
        return url('world/recipes?name='.$this->name);
    }

    /**
     * Gets the recipe's asset type for asset management.
     *
     * @return string
     */
    public function getAssetTypeAttribute() {
        return 'recipes';
    }

    /**
     * Gets the admin edit URL.
     *
     * @return string
     */
    public function getAdminUrlAttribute() {
        return url('admin/data/recipes/edit/'.$this->id);
    }

    /**
     * Gets the power required to edit this model.
     *
     * @return string
     */
    public function getAdminPowerAttribute() {
        return 'edit_data';
    }

    /**
     * Gets the currency's asset type for asset management.
     *
     * @return bool
     */
    public function getLockedAttribute() {
        return $this->needs_unlocking;
    }

    /**
     * Returns whether or not a recipe's ingredients are all currency.
     *
     * @return bool
     */
    public function getOnlyCurrencyAttribute() {
        if (count($this->ingredients)) {
            $type = [];
            foreach ($this->ingredients as $ingredientredient) {
                $type[] = $ingredientredient->ingredient_type;
            }
            $types = array_flip($type);
            if (count($types) == 1 && key($types) == 'Currency') {
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    /**
     * Returns whether or not the viewing user can craft this recipe.
     *
     * @param mixed $user
     *
     * @return bool
     */
    public function checkRecipe($user) {
        $ingredients = $this->ingredients->sortBy('ingredient_type');
        if ($this->onlyCurrency) {
            foreach ($ingredients as $ingredient) {
                $currencyCheck = UserCurrency::where('user_id', $user->id)->where('currency_id', $ingredient->ingredient->id)->first();
                if (!$currencyCheck) {
                    return false;
                } elseif ($currencyCheck->quantity < $ingredient->quantity) {
                    return false;
                }
            }
        } else {
            foreach ($ingredients as $ingredient) {
                if (!$ingredient->hasIngredient($user)) {
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * Returns whether or not a user has unlocked this recipe.
     *
     * @param mixed $user
     */
    public function hasUserUnlocked($user) {
        if (!$this->needs_unlocking && !hasLimits($this)) {
            return true;
        }

        if ($this->needs_unlocking && hasLimits($this)) {
            return Auth::user()->hasRecipe($this->id) && hasUnlockedLimits($this);
        } elseif (!$this->needs_unlocking && hasLimits($this)) {
            return hasUnlockedLimits($this);
        } elseif ($this->needs_unlocking && !hasLimits($this)) {
            return Auth::user()->hasRecipe($this->id);
        }

        return false;
    }
}
