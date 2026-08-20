<?php

namespace App\Models\Garden;

use App\Models\Model;

class GardenObject extends Model {
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'type', 'object_model', 'object_id', 'data',
    ];

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'garden_objects';

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'data' => 'array',
    ];

    /**********************************************************************************************

        RELATIONS

    **********************************************************************************************/

    /**
     * Get the user who owns the stack.
     */
    public function object() {
        return $this->belongsTo($this->object_model, 'object_id');
    }

    /**********************************************************************************************

        ATTRIBUTES

    **********************************************************************************************/

    /**
     * Get the required quality attribute.
     */
    public function getRequiredQualityAttribute() {
        return $this->data['quality'] ?? null;
    }

    /**
     * Displays the objects's rewards.
     *
     * @return string
     */
    public function getRewardsAttribute() {
        if (!isset($this->data['rewards'])) {
            return [];
        }

        $assets = parseAssetData($this->data['rewards']);
        $rewards = [];
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

        return $rewards;
    }

    /**
     * Gets the raw rewards for an animal collection outcome.
     *
     * @param string $outcome
     *
     * @return array
     */
    public function rawRewardsFor($outcome = 'stay') {
        $key = $outcome === 'remove' ? 'remove_rewards' : 'stay_rewards';

        return $this->data[$key] ?? [];
    }

    /**
     * Gets rewards formatted for the admin reward widget.
     *
     * @param string $outcome
     *
     * @return array
     */
    public function rewardsFor($outcome = 'stay') {
        $rewards = [];
        foreach (parseAssetData($this->rawRewardsFor($outcome)) as $type => $assets) {
            $class = getAssetModelString($type, false);
            foreach ($assets as $id => $asset) {
                $rewards[] = (object) [
                    'rewardable_type' => $class,
                    'rewardable_id'   => $id,
                    'quantity'        => $asset['quantity'],
                ];
            }
        }

        return $rewards;
    }

    /**
     * Displays the objects's rewards.
     *
     * @return string
     */
    public function getFoodAttribute() {
        if (!isset($this->data['food'])) {
            return [];
        }

        $assets = parseAssetData($this->data['food']);
        $rewards = [];
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

        return $rewards;
    }

    /**
     * Displays the required food as an exploded string.
     *
     * @return string
     */
    public function getDisplayFoodAttribute() {
        if (!isset($this->data['food']) || empty($this->data['food'])) {
            return null;
        }

        $assets = parseAssetData($this->data['food']);
        $display = [];
        foreach ($assets as $a) {
            foreach ($a as $asset) {
                $display[] = $asset['quantity'].'x '.$asset['asset']->displayName;
            }
        }

        return implode(', ', $display);
    }

    /**
     * Gets tools formatted for the admin reward widget.
     *
     * @return array
     */
    public function getToolsAttribute() {
        $tools = [];
        foreach (parseAssetData($this->data['tools'] ?? []) as $type => $assets) {
            foreach ($assets as $id => $asset) {
                if (!$asset['asset']) {
                    continue;
                }
                $tools[] = (object) [
                    'rewardable_type' => getAssetModelString($type, false),
                    'rewardable_id'   => $id,
                    'quantity'        => $asset['quantity'],
                    'data'            => $asset['data'],
                ];
            }
        }

        return $tools;
    }

    /**
     * Gets required tool assets for an interaction stage.
     *
     * @param int $stage
     *
     * @return array
     */
    public function toolsForStage($stage) {
        $tools = [];
        foreach (parseAssetData($this->data['tools'] ?? []) as $assets) {
            foreach ($assets as $asset) {
                if ($asset['asset'] && (int) ($asset['data']['stage'] ?? -1) === (int) $stage) {
                    $tools[] = $asset;
                }
            }
        }

        return $tools;
    }

    /**
     * Displays required tools for an interaction stage.
     *
     * @param int $stage
     *
     * @return string|null
     */
    public function displayToolsForStage($stage) {
        $display = [];
        foreach ($this->toolsForStage($stage) as $tool) {
            $display[] = $tool['quantity'].'x '.$tool['asset']->displayName;
        }

        return $display ? implode(', ', $display) : null;
    }

    /**
     * Displays the object's independently rolled potential rewards.
     *
     * @return array
     */
    public function getPotentialRewardsAttribute() {
        $rewards = [];
        foreach (parseAssetData($this->data['potential_rewards'] ?? []) as $type => $assets) {
            $class = getAssetModelString($type, false);
            foreach ($assets as $id => $asset) {
                $rewards[] = (object) [
                    'rewardable_type' => $class,
                    'rewardable_id'   => $id,
                    'quantity'        => $asset['quantity'],
                    'data'            => $asset['data'],
                ];
            }
        }

        return $rewards;
    }

    /**********************************************************************************************

        OTHER FUNCTIONS

    **********************************************************************************************/

    /**
     * Displays the object's stage images.
     *
     * @param mixed $interactions
     *
     * @return string|null
     */
    public function stageImage($interactions) {
        // find closest stage image less than or equal to current stage
        // stage's that have images are stored in data['stages']
        if (!isset($this->data['stages']) || !is_array($this->data['stages'])) {
            return null;
        }

        $stages = collect($this->data['stages'])->map(function ($stage, $key) {
            return is_numeric($stage) ? (int) $stage : (int) $key;
        })->unique()->sort()->values();

        if ($stages->contains((int) $interactions)) {
            return asset('images/data/gardens/objects/'.$this->id.'-'.$interactions.'-image.png');
        }

        $stages = $stages->filter(function ($stage) use ($interactions) {
            return $stage < $interactions;
        })->sortDesc();

        if ($stages->isNotEmpty()) {
            $closestStage = $stages->first();

            return asset('images/data/gardens/objects/'.$this->id.'-'.$closestStage.'-image.png');
        }

        return null;
    }

    /**
     * Whether this seed may be planted in the supplied month.
     *
     * @param int|null $month
     *
     * @return bool
     */
    public function isInSeason($month = null) {
        if ($this->type !== 'seed') {
            return true;
        }

        $start = $this->data['start_month'] ?? null;
        $end = $this->data['end_month'] ?? $start;
        if (!$start) {
            return true;
        }

        $month = $month ?? now()->month;

        return $start <= $end
            ? $month >= $start && $month <= $end
            : $month >= $start || $month <= $end;
    }

    /**
     * Gets the human-readable planting season for encyclopedia and admin displays.
     *
     * @return string
     */
    public function getSeasonLabelAttribute() {
        if ($this->type !== 'seed' || empty($this->data['start_month'])) {
            return 'Year-round';
        }

        $months = [
            1 => 'January', 2 => 'February', 3 => 'March', 4 => 'April',
            5 => 'May', 6 => 'June', 7 => 'July', 8 => 'August',
            9 => 'September', 10 => 'October', 11 => 'November', 12 => 'December',
        ];
        $start = (int) $this->data['start_month'];
        $end = (int) ($this->data['end_month'] ?? $start);

        if (!isset($months[$start], $months[$end])) {
            return 'Year-round';
        }

        return $start === $end ? $months[$start] : $months[$start].'–'.$months[$end];
    }
}
