<?php

namespace App\Services;

use App\Models\Element\Element;
use App\Models\Element\ElementImmunity;
use App\Models\Element\ElementWeakness;
use App\Models\Element\Typing;
use DB;

class ElementService extends Service {
    /*
    |--------------------------------------------------------------------------
    | Element Service
    |--------------------------------------------------------------------------
    |
    | Handles the creation and editing of element categories and elements.
    |
    */

    /**********************************************************************************************

        ELEMENTS

    **********************************************************************************************/

    /**
     * Creates a new element.
     *
     * @param array                 $data
     * @param \App\Models\User\User $user
     *
     * @return bool|Element
     */
    public function createElement($data, $user) {
        DB::beginTransaction();

        try {
            $data = $this->populateData($data);

            $image = null;
            if (isset($data['image']) && $data['image']) {
                $data['has_image'] = 1;
                $image = $data['image'];
                unset($data['image']);
            } else {
                $data['has_image'] = 0;
            }

            $element = Element::create($data);

            if (!$this->logAdminAction($user, 'Created Element', 'Created '.$element->displayName)) {
                throw new \Exception('Failed to log admin action.');
            }

            if ($image) {
                $this->handleImage($image, $element->imagePath, $element->imageFileName);
            }

            return $this->commitReturn($element);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }

    /**
     * Updates an element.
     *
     * @param Element               $element
     * @param array                 $data
     * @param \App\Models\User\User $user
     *
     * @return bool|Element
     */
    public function updateElement($element, $data, $user) {
        DB::beginTransaction();

        try {
            if (Element::where('name', $data['name'])->where('id', '!=', $element->id)->exists()) {
                throw new \Exception('The name has already been taken.');
            }

            $data = $this->populateData($data, $element);

            $image = null;
            if (isset($data['image']) && $data['image']) {
                $data['has_image'] = 1;
                $image = $data['image'];
                unset($data['image']);
            }

            $element->update($data);

            // create weaknesses and immunities
            if (isset($data['weakness_id']) && $data['weakness_id']) {
                foreach ($data['weakness_id'] as $key => $id) {
                    $weakness = ElementWeakness::where('element_id', $element->id)->where('weakness_id', $id)->first();
                    if ($weakness) {
                        $weakness->update([
                            'multiplier' => $data['weakness_multiplier'][$key],
                        ]);
                        $weakness->save();
                    } else {
                        ElementWeakness::create([
                            'element_id'  => $element->id,
                            'weakness_id' => $id,
                            'multiplier'  => $data['weakness_multiplier'][$key],
                        ]);
                    }
                }
            }

            if (isset($data['immunity_id']) && $data['immunity_id']) {
                foreach ($data['immunity_id'] as $id) {
                    $immunity = ElementImmunity::where('element_id', $element->id)->where('immunity_id', $id)->first();
                    if (!$immunity) {
                        ElementImmunity::create([
                            'element_id'  => $element->id,
                            'immunity_id' => $id,
                        ]);
                    }
                }
            }

            if (!$this->logAdminAction($user, 'Updated Element', 'Updated '.$element->displayName)) {
                throw new \Exception('Failed to log admin action.');
            }

            if ($element) {
                $this->handleImage($image, $element->imagePath, $element->imageFileName);
            }

            return $this->commitReturn($element);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }

    /**
     * Deletes an element.
     *
     * @param Element $element
     * @param mixed   $user
     *
     * @return bool
     */
    public function deleteElement($element, $user) {
        DB::beginTransaction();

        try {
            // check if typing exists
            $types = Typing::where('element_ids', 'like', '%'.$element->id.'%')->get();
            if ($types->count()) {
                $typeNames = [];
                foreach ($types as $type) {
                    $typeNames[] = $type->object->displayName;
                }
                throw new \Exception('Cannot delete element. It is used in the following typings: '.implode(', ', $typeNames));
            }

            if (!$this->logAdminAction($user, 'Deleted Element', 'Deleted '.$element->name)) {
                throw new \Exception('Failed to log admin action.');
            }

            if ($element->has_image) {
                $this->deleteImage($element->imagePath, $element->imageFileName);
            }
            $element->weaknesses()->delete();
            $element->immunities()->delete();
            $element->delete();

            return $this->commitReturn(true);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }

    /**
     * Processes user input for creating/updating an element.
     *
     * @param array   $data
     * @param Element $element
     *
     * @return array
     */
    private function populateData($data, $element = null) {
        if (isset($data['description']) && $data['description']) {
            $data['parsed_description'] = parse($data['description']);
        } else {
            $data['parsed_description'] = null;
        }

        if (isset($data['remove_image'])) {
            if ($element && $element->has_image && $data['remove_image']) {
                $data['has_image'] = 0;
                $this->deleteImage($element->imagePath, $element->imageFileName);
            }
            unset($data['remove_image']);
        }

        return $data;
    }
}
