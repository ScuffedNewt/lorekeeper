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

            $data = $this->populateDailyData($data);
            $daily = Daily::create($data);

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

            $data = $this->populateType($data, $daily);

            $data = $this->handleImages($data, $daily, $daily->wheel);

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
            if (isset($daily->data['has_button_image']) && $daily->data['has_button_image']) {
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
        // type agnostic data
        if (isset($data['description']) && $data['description']) {
            $data['parsed_description'] = parse($data['description']);
        } else {
            $data['parsed_description'] = null;
        }
        if (!isset($data['fee']) || !$data['fee']) {
            $data['fee'] = 0;
        }
        $data['is_active'] = isset($data['is_active']);

        // handle image removal
        if (isset($data['remove_image'])) {
            if ($daily && $daily->has_image && $data['remove_image']) {
                $data['has_image'] = 0;
                $this->deleteImage($daily->dailyImagePath, $daily->dailyImageFileName);
            }
            unset($data['remove_image']);
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
        // set segment style if it applies
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
     * Populates the daily with data specific to its type.
     *
     * @param array $data
     * @param Daily $daily
     */
    private function populateType($data, $daily) {
        if ($daily->type == 'Wheel') {
            if (isset($data['remove_wheel'])) {
                if ($daily && isset($daily->wheel->wheel_extension) && $data['remove_wheel']) {
                    $this->deleteImage($daily->wheel->imagePath, $daily->wheel->wheelFileName);
                    $data['wheel_extension'] = null;
                }
                unset($data['remove_wheel']);
            }

            if (isset($data['remove_stopper'])) {
                if ($daily && isset($daily->wheel->stopper_extension) && $data['remove_stopper']) {
                    $this->deleteImage($daily->wheel->imagePath, $daily->wheel->stopperFileName);
                    $data['stopper_extension'] = null;
                }
                unset($data['remove_stopper']);
            }

            if (isset($data['remove_background'])) {
                if ($daily && isset($daily->wheel->background_extension) && $data['remove_background']) {
                    $this->deleteImage($daily->wheel->imagePath, $daily->wheel->backgroundFileName);
                    $data['background_extension'] = null;
                }
                unset($data['remove_background']);
            }

            $wheel = DailyWheel::firstOrNew(['daily_id' => $daily->id]);
            $wheel->update([
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
        } elseif ($daily->type == 'Button') {
            if (isset($data['remove_button_image'])) {
                if ($daily && isset($daily->data['has_button_image']) && $data['remove_button_image']) {
                    $this->deleteImage($daily->dailyImagePath, $daily->buttonImageFileName);
                }
                unset($data['remove_button_image']);
            }
        }

        // no other population needed for other types

        return $data;
    }

    /**
     * Handles image uploads.
     *
     * @param mixed      $data
     * @param mixed      $daily
     * @param mixed|null $wheel
     */
    private function handleImages($data, $daily, $wheel = null) {
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
            if (isset($data['data']['button_image']) && $data['data']['button_image']) {
                $data['data']['has_button_image'] = 1;
                $buttonImage = $data['data']['button_image'];
                unset($data['data']['button_image']);
            }

            if ($buttonImage) {
                $this->handleImage($buttonImage, $daily->dailyImagePath, $daily->buttonImageFileName);
            }
        }

        if ($daily->type == 'Wheel') {
            $wheelImage = null;
            if (isset($data['data']['wheel_image']) && $data['data']['wheel_image']) {
                $wheelImage = $data['data']['wheel_image'];
                unset($data['data']['wheel_image']);
            }
            $stopperImage = null;
            if (isset($data['data']['stopper_image']) && $data['data']['stopper_image']) {
                $stopperImage = $data['data']['stopper_image'];
                unset($data['data']['stopper_image']);
            }

            $backgroundImage = null;
            if (isset($data['data']['background_image']) && $data['data']['background_image']) {
                $backgroundImage = $data['data']['background_image'];
                unset($data['data']['background_image']);
            }

            if ($wheel) {
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
        }

        return $data;
    }
}
