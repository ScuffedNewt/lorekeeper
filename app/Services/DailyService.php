<?php

namespace App\Services;

use App\Models\Daily\Daily;
use App\Models\Daily\DailyReward;
use App\Models\Daily\DailyWheel;
use DB;
use Illuminate\Support\Arr;

class DailyService extends Service {
    /*
    |--------------------------------------------------------------------------
    | Daily Service
    |--------------------------------------------------------------------------
    |
    | Handles the creation and editing of dailies.
    |
    */

    /**********************************************************************************************

        DAILIES

     **********************************************************************************************/

    /**
     * Creates a new daily.
     *
     * @param array $data
     *
     * @return bool|Daily
     */
    public function createDaily($data) {
        DB::beginTransaction();

        try {
            // More specific validation
            if (Daily::where('name', $data['name'])->exists()) {
                throw new \Exception('The name has already been taken.');
            }
            $data['is_loop'] = 1; //we like looping dailies
            $data = $this->populateDailyData($data);

            $daily = Daily::create($data);

            if ($daily->type == 'Wheel') {
                $this->populateWheel($data, $daily);
            }

            return $this->commitReturn($daily);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }

    /**
     * Updates a daily.
     *
     * @param Daily $daily
     * @param array $data
     *
     * @return bool|Daily
     */
    public function updateDaily($daily, $data) {
        DB::beginTransaction();

        try {
            // More specific validation
            if (Daily::where('name', $data['name'])->where('id', '!=', $daily->id)->exists()) {
                throw new \Exception('The name has already been taken.');
            }

            $data = $this->populateDailyData($data, $daily);

            $wheel = null;
            if ($daily->type == 'Wheel') {
                $wheel = $this->populateWheel($data, $daily);
            }

            $data['is_timed_daily'] = isset($data['is_timed_daily']);
            $data = $this->handleImages($data, $daily, $wheel);
            $daily->update($data);
            $this->populateRewards(Arr::only($data, ['rewardable_type', 'rewardable_id', 'quantity', 'step']), $daily);

            return $this->commitReturn($daily);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }

    /**
     * Deletes a daily.
     *
     * @param Daily $daily
     *
     * @return bool
     */
    public function deleteDaily($daily) {
        DB::beginTransaction();

        try {
            if ($daily->has_image) {
                $this->deleteImage($daily->dailyImagePath, $daily->dailyImageFileName);
            }
            if ($daily->has_button_image) {
                $this->deleteImage($daily->dailyImagePath, $daily->buttonyImageFileName);
            }

            if ($daily->wheel) {
                $wheel = $daily->wheel;
                if ($wheel->wheel_extension) {
                    $this->deleteImage($wheel->imagePath, $wheel->wheelFileName);
                }
                if ($wheel->stopper_extension) {
                    $this->deleteImage($wheel->imagePath, $wheel->stopperFileName);
                }
                if ($wheel->background_extension) {
                    $this->deleteImage($wheel->imagePath, $wheel->backgroundFileName);
                }
                $wheel->delete();
            }

            $daily->rewards()->delete();
            $daily->timers()->delete();
            $daily->delete();

            return $this->commitReturn(true);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }

    /**
     * Sorts daily order.
     *
     * @param array $data
     *
     * @return bool
     */
    public function sortDaily($data) {
        DB::beginTransaction();

        try {
            // explode the sort array and reverse it since the order is inverted
            $sort = array_reverse(explode(',', $data));

            foreach ($sort as $key => $s) {
                Daily::where('id', $s)->update(['sort' => $key]);
            }

            return $this->commitReturn(true);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }

    /**
     * Processes user input for creating/updating a daily.
     *
     * @param array $data
     * @param Daily $daily
     *
     * @return array
     */
    private function populateDailyData($data, $daily = null) {
        if (isset($data['description']) && $data['description']) {
            $data['parsed_description'] = parse($data['description']);
        }
        $data['is_active'] = isset($data['is_active']);
        $data['is_loop'] = isset($data['is_loop']) || (isset($daily) && $daily->type == 'Wheel');
        $data['is_streak'] = isset($data['is_streak']);
        if ($data['fee'] == null) {
            $data['fee'] = 0;
        }

        //set progressable automatically
        if (isset($data['step'])) {
            $steps = array_unique($data['step']);
            if (count($steps) > 1) {
                $data['is_progressable'] = 1;
            }
            if (count($steps) == 1) {
                $data['is_progressable'] = 0;
            }
        } else {
            $data['is_progressable'] = 0;
        }

        //handle image removal
        if (isset($data['remove_image'])) {
            if ($daily && $daily->has_image && $data['remove_image']) {
                $data['has_image'] = 0;
                $this->deleteImage($daily->dailyImagePath, $daily->dailyImageFileName);
            }
            unset($data['remove_image']);
        }

        if (isset($data['remove_button_image'])) {
            if ($daily && $daily->has_button_image && $data['remove_button_image']) {
                $data['has_button_image'] = 0;
                $this->deleteImage($daily->dailyImagePath, $daily->buttonImageFileName);
            }
            unset($data['remove_button_image']);
        }

        if (isset($data['remove_wheel'])) {
            if ($daily && isset($daily->wheel->wheel_extension) && $data['remove_wheel']) {
                $this->deleteImage($daily->wheel->imagePath, $daily->wheel->wheelFileName);
                $daily->wheel->wheel_extension = null;
            }
            unset($data['remove_wheel']);
        }

        if (isset($data['remove_stopper'])) {
            if ($daily && isset($daily->wheel->stopper_extension) && $data['remove_stopper']) {
                $this->deleteImage($daily->wheel->imagePath, $daily->wheel->stopperFileName);
                $daily->wheel->stopper_extension = null;
            }
            unset($data['remove_stopper']);
        }

        if (isset($data['remove_background'])) {
            if ($daily && isset($daily->wheel->background_extension) && $data['remove_background']) {
                $this->deleteImage($daily->wheel->imagePath, $daily->wheel->backgroundFileName);
                $daily->wheel->background_extension = null;
            }
            unset($data['remove_background']);
        }

        return $data;
    }

    /**
     * Saves segment style in the format needed for the whinwheel library.
     *
     * @param mixed $data
     */
    private function populateSegmentStyle($data) {
        $styleObject = [];
        //set segment style if it applies
        if (isset($data['segment_style'])) {
            for ($i = 0; $i < $data['segment_number']; $i++) {
                $styleObject[] = [
                    'fillStyle' => $data['segment_style']['color'][$i] ?? null,
                    'text'      => $data['segment_style']['text'][$i] ?? null,
                    'number'    => $i + 1,
                ];
            }
        }

        return json_encode($styleObject);
    }

    /**
     * Processes user input for creating/updating daily rewards.
     *
     * @param array $data
     * @param Daily $daily
     */
    private function populateRewards($data, $daily) {
        // Clear the old rewards...
        $daily->rewards()->delete();
        if (isset($data['rewardable_type'])) {
            foreach ($data['rewardable_type'] as $key => $type) {
                if ($type != null) {
                    DailyReward::create([
                        'daily_id'        => $daily->id,
                        'rewardable_type' => $type,
                        'rewardable_id'   => $data['rewardable_id'][$key],
                        'quantity'        => $data['quantity'][$key],
                        'step'            => $data['step'][$key],
                    ]);
                }
            }
        }
    }

    /**
     * Populates the wheel with data.
     *
     * @param array $data
     * @param Daily $daily
     */
    private function populateWheel($data, $daily) {
        // 'daily_id', 'wheel_extension', 'background_extension', 'stopper_extension', 'size', 'alignment', 'segment_number', 'segment_style', 'text_orientation', 'text_fontsize'
        if ($daily->wheel) {
            $daily->wheel->update([
                'size'             => $data['size'],
                'alignment'        => $data['alignment'],
                'segment_number'   => $data['segment_number'],
                'segment_style'    => $this->populateSegmentStyle($data),
                'text_orientation' => $data['text_orientation'],
                'text_fontsize'    => $data['text_fontsize'],
            ]);

            return $daily->wheel;
        } else {
            $wheel = DailyWheel::create([
                'daily_id'             => $daily->id,
                'wheel_extension'      => $data['wheel_extension'] ?? null,
                'background_extension' => $data['background_extension'] ?? null,
                'stopper_extension'    => $data['stopper_extension'] ?? null,
                'size'                 => $data['size'] ?? 400,
                'alignment'            => $data['alignment'] ?? 'center',
                'segment_number'       => $data['segment_number'] ?? 4,
                'segment_style'        => $this->populateSegmentStyle($data),
                'text_orientation'     => $data['text_orientation'] ?? 'curved',
                'text_fontsize'        => $data['text_fontsize'] ?? '18',
            ]);

            return $wheel;
        }
    }

    private function handleImages($data, $daily, $wheel) {
        $image = null;
        if (isset($data['image']) && $data['image']) {
            $data['has_image'] = 1;
            $image = $data['image'];
            unset($data['image']);
        }
        if ($image) {
            $this->handleImage($image, $daily->dailyImagePath, $daily->dailyImageFileName);
        }

        if ($daily->type == 'Button') {
            $buttonImage = null;
            if (isset($data['button_image']) && $data['button_image']) {
                $data['has_button_image'] = 1;
                $buttonImage = $data['button_image'];
                unset($data['button_image']);
            }

            if ($buttonImage) {
                $this->handleImage($buttonImage, $daily->dailyImagePath, $daily->buttonImageFileName);
            }
        }

        if ($daily->type == 'Wheel') {
            $wheelImage = null;
            if (isset($data['wheel_image']) && $data['wheel_image']) {
                $wheelImage = $data['wheel_image'];
                unset($data['wheel_image']);
            }

            $stopperImage = null;
            if (isset($data['stopper_image']) && $data['stopper_image']) {
                $stopperImage = $data['stopper_image'];
                unset($data['stopper_image']);
            }

            $backgroundImage = null;
            if (isset($data['background_image']) && $data['background_image']) {
                $backgroundImage = $data['background_image'];
                unset($data['background_image']);
            }
            if ($wheelImage) {
                $wheel->wheel_extension = $wheelImage->getClientOriginalExtension();
                $this->handleImage($wheelImage, $wheel->imagePath, $wheel->wheelFileName, null);
            }
            if ($stopperImage) {
                $wheel->stopper_extension = $stopperImage->getClientOriginalExtension();
                $this->handleImage($stopperImage, $wheel->imagePath, $wheel->stopperFileName, null);
            }
            if ($backgroundImage) {
                $wheel->background_extension = $backgroundImage->getClientOriginalExtension();
                $this->handleImage($backgroundImage, $wheel->imagePath, $wheel->backgroundFileName, null);
            }
            $wheel->save();
        }

        return $data;
    }
}
