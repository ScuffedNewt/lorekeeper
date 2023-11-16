<?php

namespace App\Services;

use App\Models\Currency\Currency;
use App\Models\Item\Item;
use App\Models\User\UserGardenPlot;
use App\Models\User\UserItem;
use Carbon\Carbon;
use DB;
use Log;

class PlotManager extends Service {
    /**
     * purchases a plot for a user.
     *
     * @param \App\Models\User\User $user
     * @param mixed                 $plot
     *
     * @return \App\Models\Plot\Plot|bool
     */
    public function purchasePlot($plot, $user) {
        DB::beginTransaction();

        try {
            if (!$plot) {
                throw new \Exception('Not a valid plot.');
            }

            if ($plot->isFree) {
                if (!$this->createPlot($user, $plot)) {
                    throw new \Exception('Error creating plot for user.');
                }
            } else {
                $service = new CurrencyManager;

                $currency = Currency::find($plot->currency_id);
                if (!$currency) {
                    throw new \Exception('Could not find currency');
                }
                $quantity = $plot->plot_cost;

                if ($quantity <= 0) {
                    throw new \Exception('Quantity cannot be 0 or less.');
                }
                if (!$service->debitCurrency($user, null, 'Bought Garden Plot', 'Bought Crafing Plot #'.$plot->id.' for '.$plot->plot_cost.' '.$currency->name, $currency, $quantity)) {
                    throw new \Exception('Not enough currency to buy this.');
                }
                if (!$this->createPlot($user, $plot)) {
                    throw new \Exception('Error creating plot for user.');
                }
            }

            return $this->commitReturn($plot);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }

    /**
     * creates a plot for a user.
     *
     * @param mixed $user
     * @param mixed $plot
     */
    public function createPlot($user, $plot) {
        DB::beginTransaction();

        try {
            if (!$plot) {
                throw new \Exception('Not a valid plot.');
            }

            $madePlot = UserGardenPlot::create([
                'user_id' => $user->id,
                'plot_id' => $plot->id,
            ]);

            return $this->commitReturn($madePlot);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }

    /**
     * Collects all of a certain type.
     *
     * @param mixed $user
     * @param mixed $type
     */
    public function collectAll($user, $type) {
        $plots = UserGardenPlot::where('user_id', $user->id)->whereRelation('plot', 'plot_type', $type)->where('item_id', '!=', null)->get();
        $count = 0;
        foreach ($plots as $plot) {
            if ($plot->readyToClaim) {
                $this->claim($user, $plot);
                $count++;
            }
        }

        return $count;
    }

    /***************************************************************************************************************
     *
     * GARDENING RELATED
     *
     ***************************************************************************************************************/

    /**
     * plants a seed.
     *
     * @param mixed $user
     * @param mixed $data
     */
    public function place($user, $data) {
        DB::beginTransaction();

        try {
            $plot = UserGardenPlot::findOrFail($data['plot_id']);
            if (!$plot) {
                throw new \Exception('Invalid plot.');
            }

            if (config('lorekeeper.garden.'.$plot->plot->plot_type.'.type') == 'item') {
                $plot_obj = UserItem::where('item_id', $data['item_id'])->where('count', '>', 0)->where('user_id', $user->id)->first();
                if (!$plot_obj) {
                    throw new \Exception('Invalid item.');
                }
            }

            // check if we are consuming the item
            if (config('lorekeeper.garden.'.$plot->plot->plot_type.'.is_consumed')) {
                // we can use assets to debit the item just by setting the quantity to one,
                // for now we will use the inventory manager to do this since no other logic is needed

                // only seeds are debitted right now but lets be nice to future me! and future you!
                $type = config('lorekeeper.garden.'.$plot->plot->plot_type.'.place_word').($plot->plot->plot_type == 'seed' ? 'ed' : 'd');
                if (!(new InventoryManager)->debitStack($user, 'Garden', ['data' => $type.' in the Garden'], $plot_obj, 1)) {
                    throw new \Exception('Could not debit item.');
                }
            } else {
                // if not consuming it we're upping the garden count to make it unavailable for other plots
                // find the stack where count is greater than 0 and garden count is less than count
                $plot_obj = UserItem::where('item_id', $data['item_id'])->where('count', '>', 0)->where('garden_count', '<=', 'count')->where('user_id', $user->id)->first();
                Log::info(['plot_obj', json_encode($plot_obj)]);
                if (!$plot_obj) {
                    throw new \Exception("You don't have enough of this item.");
                }
                $plot_obj->garden_count++;
                $plot_obj->save();
            }

            // check if plant needs a certain quality of modifier
            if ($plot->plot->plot_type == 'seed' && isset($plot_obj->item->tags->where('tag', 'seed')->first()->data['quality'])) {
                $quality = $plot_obj->item->tags->where('tag', 'seed')->first()->data['quality'];
                $mods = json_decode($plot->modifiers, true);
                if (!$mods) {
                    throw new \Exception('No fertiliser attached to this plot.');
                }
                if ($mods['fertiliser']['quality'] < $quality) {
                    throw new \Exception('You need a higher quality fertiliser to plant this seed.');
                }
                // check if we need to reduce the time
                if ($mods['fertiliser']['quality'] > $quality) {
                    if ($mods['fertiliser']['time_reduced']) {
                        if ($mods['fertiliser']['formula'] == 0) {
                            $time = $mods['fertiliser']['time_reduced'];
                        } else {
                            $time = ($mods['fertiliser']['time_reduced'] / $quality) * $mods['fertiliser']['time_reduced'];
                        }
                    }
                }
            }

            // check if the obj needs to be placed in a certain month (seed only rn)
            if (config('lorekeeper.garden.'.$plot->plot->plot_type.'.has_seasons')) {
                // check current month against start and end months
                $start_month = $plot_obj->item->tags->where('tag', 'seed')->first()->data['start_month'] ?
                    Carbon::create()->day(1)->month($plot_obj->item->tags->where('tag', 'seed')->first()->data['start_month']) : null;
                $end_month = $plot_obj->item->tags->where('tag', 'seed')->first()->data['end_month'] ?
                    Carbon::create()->month($plot_obj->item->tags->where('tag', 'seed')->first()->data['end_month'])->endOfMonth() : null;
                // check if current month is between start and end
                if ($start_month && $end_month) {
                    if (!Carbon::now()->between($start_month, $end_month)) {
                        throw new \Exception('This seed can only be planted between '.$start_month->format('F d').' and '.$end_month->format('F d').'.');
                    }
                }
            }

            $plot->update([
                'item_id'    => $plot_obj->item->id, // this will need to be dynamic for other types of objs
                'started_at' => Carbon::now(),
                'water_at'   => Carbon::now()->addHours(config('lorekeeper.garden.'.$plot->plot->plot_type.'.action_cooldown') - ($time ?? 0)),
                'waterings'  => 0,
                'is_dead'    => 0,
                'time'       => config('lorekeeper.garden.'.$plot->plot->plot_type.'.action_cooldown') - ($time ?? 0), // set time for easier calc later
            ]);

            return $this->commitReturn(true);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }

    /**
     * performs an action on a plot, agnostic of the type of plot.
     *
     * @param mixed $user
     * @param mixed $plot
     * @param mixed $all
     */
    public function action($user, $plot, $all = false) {
        DB::beginTransaction();

        try {
            if (!$plot) {
                throw new \Exception('Invalid plot.');
            }
            // all is a flag to water all plots of a certain type
            if (!$all) {
                // check that we are not exceeding the max waterings
                if ($plot->waterings >= $plot->actionsRequired) {
                    throw new \Exception('You have cared for this plot enough!');
                }
                $plot->waterings += 1;
                $plot->water_at = Carbon::now()->addHours($plot->time);
                $plot->save();
            } else {
                // $plot in this case is str
                $plots = UserGardenPlot::where('user_id', $user->id)->whereRelation('plot', 'plot_type', $plot)
                    ->get()->filter(function ($plot) {
                        return $plot->actionReady;
                    });
                $count = 0;
                foreach ($plots as $plot) {
                    if ($plot->waterings >= $plot->actionsRequired) {
                        continue;
                    }
                    $plot->waterings += 1;
                    $plot->water_at = Carbon::now()->addHours($plot->time);
                    $plot->save();
                    $count++;
                }
                flash($count.' plots affected.')->success();
            }

            return $this->commitReturn(true);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }

    /**
     * clears a plot, agnostic of the type of plot, generally only used for seeds.
     *
     * @param mixed $user
     * @param mixed $plot
     */
    public function clear($user, $plot) {
        DB::beginTransaction();

        try {
            if (!$plot) {
                throw new \Exception('Invalid plot.');
            }
            if (!config('lorekeeper.garden.'.strtolower($plot->plot->plot_type).'.is_consumed') && config('lorekeeper.garden.'.strtolower($plot->plot->plot_type).'.type') == 'item') {
                $item = UserItem::where('item_id', $plot->item_id)->where('user_id', $user->id)->where('count', '>', 0)->where('garden_count', '>', 0)->first();
                if (!$item) {
                    throw new \Exception('Invalid item.');
                }

                $item->garden_count--;
                $item->save();
            }

            $plot->update([
                'item_id'    => null,
                'modifiers'  => null,
                'started_at' => null,
                'water_at'   => null,
                'waterings'  => 0,
                'is_dead'    => 0,
                'time'       => null,
            ]);

            return $this->commitReturn(true);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }

    /**
     * Adds modifiers to the plot.
     *
     * @param mixed $user
     * @param mixed $data
     */
    public function modify($user, $data) {
        DB::beginTransaction();

        try {
            $plot = UserGardenPlot::findOrFail($data['plot_id']);
            $mod = UserItem::where('item_id', $data['mod_id'])->where('user_id', $user->id)->where('count', '>', 0)->first();
            if (!$mod) {
                throw new \Exception('Invalid modifier.');
            }

            $invman = new InventoryManager;
            if (!$invman->debitStack($user, 'Added Modifier to Garden', ['data' => 'Modifier used in the Garden'], $mod, 1)) {
                throw new \Exception('Could not debit modifier.');
            }

            $tag = $mod->item->tags()->where('tag', 'fertiliser')->first();
            if (!$tag) {
                throw new \Exception('No data attached to this modifier.');
            }

            $current = json_decode($plot->modifiers, true);
            if (!$current) {
                $current['fertiliser'] = [
                    'quality'      => $tag->data['quality'],
                    'time_reduced' => $tag->data['time_reduced'] ?? 0,
                    'formula'      => $tag->data['formula'] ?? null,
                ];
                if ($current['fertiliser']['quality'] > config('lorekeeper.garden.seed.max_quality')) {
                    throw new \Exception('You have reached the maximum quality for this plot.');
                }
            } else {
                throw new \Exception('You have already used a modifier on this plot.');
            }

            $plot->modifiers = json_encode($current);
            $plot->save();

            return $this->commitReturn(true);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }

    /**
     * claim a plot, agnostic of the type of plot.
     *
     * @param mixed $user
     * @param mixed $plot
     * @param mixed $remove
     */
    public function claim($user, $plot, $remove = false) {
        DB::beginTransaction();

        try {
            // first get all the rewards to be given to the user
            $assets = parseAssetData($plot->item->tags->where('tag', $plot->plot->plot_type)->first()->data['rewards']);
            if (!$rewards = fillUserAssets($assets, $user, $user, 'Garden Harvest', [
                'data' => 'Received rewards from '.strtolower(config('lorekeeper.garden.'.$plot->plot->plot_type.'.finish_word')).'ing '.$plot->plot->plot_type,
            ])) {
                throw new \Exception('Failed to credit items.');
            }

            // check for if we remove and return the object in the plot
            if (!config('lorekeeper.garden.'.$plot->plot->plot_type.'.is_consumed') && $remove) {
                if (config('lorekeeper.garden.'.$plot->plot->plot_type.'.type' == 'item')) {
                    $item = UserItem::where('item_id', $plot->item_id)->where('user_id', $user->id)->where('count', '>', 0)->where('garden_count', '>', 0)->first();
                    if (!$item) {
                        throw new \Exception('Invalid item.');
                    }

                    $item->garden_count--;
                    $item->save();
                }
            }

            // check if user is going to get the bonus reward
            if (isset($plot->item->tags->where('tag', $plot->plot->plot_type)->first()->getData()['potential_rewards'])) {
                $bonus = $plot->item->tags->where('tag', $plot->plot->plot_type)->first()->getData()['bonus_chance'];
                if ($bonus - rand(0, 100) > 0) {
                    $assets = parseAssetData($plot->item->tags->where('tag', $plot->plot->plot_type)->first()->data['potential_rewards']);
                    if (!$bonus = fillUserAssets($assets, $user, $user, 'Bonus Harvest', [
                        'data' => 'Received bonus rewards from '.strtolower(config('lorekeeper.garden.'.$plot->plot->plot_type.'.finish_word')).'ing '.$plot->plot->plot_type,
                    ])) {
                        throw new \Exception('Failed to credit items.');
                    }
                }
                flash('Score! You got some extra goodies.<br>'.$this->getRewardsString($bonus))->success();
            }

            $plot->update([
                'item_id'    => (!$remove && config('lorekeeper.garden.'.$plot->plot->plot_type.'.is_consumed')) ? null : $plot->item_id,
                'modifiers'  => null,
                'started_at' => (!$remove && config('lorekeeper.garden.'.$plot->plot->plot_type.'.is_consumed')) ? null : Carbon::now(),
                'water_at'   => (!$remove && config('lorekeeper.garden.'.$plot->plot->plot_type.'.is_consumed')) ? null : Carbon::now()->addHours(config('lorekeeper.garden.'.$plot->plot->plot_type.'.action_cooldown')),
                'waterings'  => 0,
                'is_dead'    => 0,
                'time'       => (!$remove && config('lorekeeper.garden.'.$plot->plot->plot_type.'.is_consumed')) ? null : config('lorekeeper.garden.'.$plot->plot->plot_type.'.action_cooldown'),
            ]);

            flash($this->getRewardsString($rewards))->success();

            return $this->commitReturn(true);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }

    /**
     * returns reward string.
     *
     * @param mixed $rewards
     */
    private function getRewardsString($rewards) {
        $results = 'You have received: ';
        $result_elements = [];
        foreach ($rewards as $assetType) {
            if (isset($assetType)) {
                foreach ($assetType as $asset) {
                    array_push($result_elements, $asset['asset']->name.(class_basename($asset['asset']) == 'Raffle' ? ' (Raffle Ticket)' : '').' x'.$asset['quantity']);
                }
            }
        }

        return $results.implode(', ', $result_elements);
    }
}
