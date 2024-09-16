<?php

namespace App\Services\Stat;

use App\Models\Stat\Stat;
use App\Services\Service;
use DB;

class StatService extends Service {
    /**
     * Creates a new stat.
     *
     * @param mixed $data
     */
    public function createStat($data) {
        DB::beginTransaction();

        try {
            if (!isset($data['name'])) {
                throw new \Exception('Please provide a name for the stat.');
            }
            if (!isset($data['base'])) {
                throw new \Exception('Please set a base stat value.');
            }
            if (isset($data['colour'])) {
                // if colour is white, set to null
                // use regex since there might be fewer or greater f
                if (preg_match('/^#?ffffff$/i', $data['colour'])) {
                    $data['colour'] = null;
                } else {
                    // if colour is not white, make sure it's a valid hex colour
                    if (!preg_match('/^#?[0-9a-fA-F]{6}$/i', $data['colour'])) {
                        throw new \Exception('Please provide a valid hex colour.');
                    }
                }
            }

            $stat = Stat::create($data);

            return $this->commitReturn($stat);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }

    /**
     * Updates a stat.
     *
     * @param mixed $stat
     * @param mixed $data
     */
    public function updateStat($stat, $data) {
        DB::beginTransaction();

        try {
            if (!isset($data['name'])) {
                throw new \Exception('Please provide a name for the stat.');
            }
            if (!isset($data['base'])) {
                throw new \Exception('Please set a base stat value.');
            }
            if (isset($data['colour'])) {
                // if colour is white, set to null
                // use regex since there might be fewer or greater f
                if (preg_match('/^#?ffffff$/i', $data['colour'])) {
                    $data['colour'] = null;
                } else {
                    // if colour is not white, make sure it's a valid hex colour
                    if (!preg_match('/^#?[0-9a-fA-F]{6}$/i', $data['colour'])) {
                        throw new \Exception('Please provide a valid hex colour.');
                    }
                }
            }

            // check species_ids
            $stat->limits()->delete();
            if (isset($data['types']) && $data['types']) {
                foreach ($data['types'] as $key=>$type) {
                    if (!isset($data['type_ids'][$key]) || !$data['type_ids'][$key]) {
                        throw new \Exception('Please select at least one '.$type.'.');
                    }
                    $stat->limits()->create([
                        'species_id' => $data['type_ids'][$key],
                        'type'       => 'stat',
                        'type_id'    => $stat->id,
                        'is_subtype' => $type == 'subtype' ? 1 : 0,
                    ]);
                }
            }

            $base_data = [];
            if (isset($data['base_type_ids']) && $data['base_type_ids']) {
                foreach ($data['base_types'] as $key=>$type) {
                    if (!isset($data['base_type_ids'][$key]) || !$data['base_type_ids'][$key]) {
                        throw new \Exception('Please select at least one '.$type.'.');
                    }
                    // store the base type data
                    $base_data[$type][$data['base_type_ids'][$key]] = $data['base_values'][$key];
                }
            }
            $data['data']['bases'] = $base_data;

            $stat->update($data);

            return $this->commitReturn($stat);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }

    /**
     * Deletes a stat.
     *
     * @param mixed $stat
     */
    public function deleteStat($stat) {
        DB::beginTransaction();

        try {
            // Check first if the stat is currently owned or if some other site feature uses it
            if (DB::table('character_stats')->where('stat_id', $stat->id)->exists()) {
                throw new \Exception('A character currently has this stat.');
            }

            $stat->delete();

            return $this->commitReturn(true);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }
}
