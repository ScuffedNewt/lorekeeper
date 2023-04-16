<?php namespace App\Services;

use App\Services\Service;

use DB;
use Log;
use Config;
use Carbon\Carbon;

use App\Models\GardenPlot;
use App\Models\Currency\Currency;
use App\Models\User\UserGardenPlot;
use App\Services\CurrencyManager;

use App\Models\Item\Item;
use App\Models\User\UserItem;
class PlotManager extends Service
{   
    /**
     * purchases a plot for a user
     *
     * @param  array                  $data
     * @param  \App\Models\User\User  $user
     * @return bool|\App\Models\Plot\Plot
     */
    public function purchasePlot($plot, $user)
    {
        DB::beginTransaction();

        try {

            if(!$plot) throw new \Exception('Not a valid plot.');
            
            if($plot->isFree)
            {
                if(!$this->createPlot($user, $plot)) throw new \Exception('Error creating plot for user.');
            }
            else {

                $service = new CurrencyManager;

                $currency = Currency::find($plot->currency_id);
                if(!$currency) throw new \Exception('Could not find currency');
                $quantity = $plot->plot_cost;

                if($quantity <= 0) throw new \Exception('Quantity cannot be 0 or less.');

                if(!$service->debitCurrency($user, null, 'Bought Garden Plot', 'Bought Crafing Plot #' . $plot->id .' for ' . $plot->plot_cost . ' ' . $currency->name, $currency, $quantity)) throw new \Exception('Not enough currency to buy this.');

                if(!$this->createPlot($user, $plot)) throw new \Exception('Error creating plot for user.');

            }

            return $this->commitReturn($plot);
        } catch(\Exception $e) {
            $this->setError('error', $e->getMessage());
        }
        return $this->rollbackReturn(false);
    }

    /**
     * 
     */
    public function createPlot($user, $plot)
    {
        DB::beginTransaction();

        try {
            if(!$plot) throw new \Exception('Not a valid plot.');

            $madePlot = UserGardenPlot::create([
                'user_id' => $user->id,
                'plot_id' => $plot->id,
            ]);

            return $this->commitReturn($madePlot);
        } catch(\Exception $e) {
            $this->setError('error', $e->getMessage());
        }
        return $this->rollbackReturn(false);
    }

    /**
     * Collects all of a certain type
     */
    public function collectAll($user, $type)
    {
        $plots = UserGardenPlot::where('user_id', $user->id)->whereRelation('plot', 'plot_type', $type)->where('item_id', '!=', null)->get();
        $count = 0;
        foreach($plots as $plot) {
            if($plot->readyToClaim) {
                if($type == 'Seed') { $this->claim($user, $plot); }
                else { $this->animalClaim($user, $plot); }
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
     * 
     */
    public function plant($user, $data)
    {
        DB::beginTransaction();

        try {   

            $plot = UserGardenPlot::find($data['plotID']);
            if(!$plot) throw new \Exception("Invalid plot.");

            $seed = UserItem::where('item_id', $data['seedID'])->where('count', '>', 0)->where('user_id', $user->id)->first();
            if(!$seed) throw new \Exception("Invalid seed.");
            
            $invman = new InventoryManager;
            if(!$invman->debitStack($user, 'Seed Planted', ['data' => 'Seed planted in garden.'], $seed, 1)) throw new \Exception('Could not debit seed.');

            $plot->item_id = $seed->item->id;
            $plot->started_at = Carbon::now();
            // this is where we set the plot time between each waterings
            // because this is a function we can add things later
            if($plot->modifiers)
            {
                $mods = json_decode($plot->modifiers, true);
                if($mods['fertiliser'])
                {
                    $time = 24 - $mods['fertiliser'];
                }
            }
            else {
                $time = 24;
            }
            $plot->time = $time; // default 24 hours
            $plot->waterings = 0; // the user can water immediately, but we must set a base line to increment
            $plot->water_at = Carbon::now()->addHours($plot->time);
            $plot->save();

            return $this->commitReturn(true);
        } catch(\Exception $e) {
            $this->setError('error', $e->getMessage());
        }
        return $this->rollbackReturn(false);
    }

    /**
     * 
     */
    public function water($user, $data, $all = false)
    {
        DB::beginTransaction();

        try { 
            if(!$all) {
            $plot = UserGardenPlot::find($data);
            if(!$plot) throw new \Exception("Invalid plot.");

            $plot->waterings += 1;
            $plot->water_at = Carbon::now()->addHours($plot->time);
            $plot->save();
            }
            else {
                $plots = UserGardenPlot::where('user_id', $user->id)->where('water_at', '<', Carbon::now())->get();
                foreach($plots as $plot)
                {
                    $plot->waterings += 1;
                    $plot->water_at = Carbon::now()->addHours($plot->time);
                    $plot->save();
                }
            }

            return $this->commitReturn(true);
        } catch(\Exception $e) {
            $this->setError('error', $e->getMessage());
        }
        return $this->rollbackReturn(false);
    }

    /**
     * 
     */
    public function clear($user, $plot)
    {
        DB::beginTransaction();

        try { 
            if(!$plot) throw new \Exception("Invalid plot.");
            if($plot->plot->plot_type != 'Seed') {
                $item = UserItem::where('item_id', $plot->item_id)->where('user_id', $user->id)->where('count', '>', 0)->where('garden_count', '>', 0)->first();
                if(!$item) throw new \Exception("Invalid item.");

                $item->garden_count--;
                $item->save();
            }

            $plot->item_id = null;
            $plot->modifiers = null;
            $plot->started_at = null;
            $plot->water_at = null;
            $plot->waterings = 0;
            $plot->is_dead = 0;
            $plot->time = null;
            $plot->save();

            return $this->commitReturn(true);
        } catch(\Exception $e) {
            $this->setError('error', $e->getMessage());
        }
        return $this->rollbackReturn(false);
    }

    /**
     * Adds modifiers to the plot
     */
    public function mod($user, $data)
    {
        DB::beginTransaction();

        try { 

            $plot = UserGardenPlot::find($data['plotID']);
            if(!$plot) throw new \Exception("Invalid plot.");

            $mod = UserItem::where('item_id', $data['modID'])->where('user_id', $user->id)->where('count', '>', 0)->first();
            if(!$mod) throw new \Exception("Invalid modifier.");

            $invman = new InventoryManager;
            if(!$invman->debitStack($user, 'Added Modifier to Garden', ['data' => 'Modifier used in garden.'], $mod, 1)) throw new \Exception('Could not debit modifier.');

            $tag = $mod->item->tags()->where('tag', 'fertiliser')->first();
            if(!$tag) throw new \Exception("No data attached to this modifier.");

            $current = json_decode($plot->modifiers, true);
            if($current == null) {
                $current = [];
                $current['fertiliser'] = null;
            }
            else {
                if($current['fertiliser'] == 5) throw new \Exception('Max quality reached!');
            }
            $current['fertiliser'] += $tag['data']['quality'];

            $plot->modifiers = json_encode($current);
            $plot->save();

            return $this->commitReturn(true);
        } catch(\Exception $e) {
            $this->setError('error', $e->getMessage());
        }
        return $this->rollbackReturn(false);
    }

    /**
     * OwO
     */
    public function claim($user, $plot)
    {
        DB::beginTransaction();

        try { 
            $tag = $plot->item->tags()->where('tag', 'seed')->first();
            $item = Item::find($tag['data']['plant_id']);

            if($tag['data']['quantity']) $quantity = mt_rand(1, $tag['data']['quantity']);
            else $quantity = 1;

            $invman = new InventoryManager;
            if(!$invman->creditItem($user, $user, 'Garden Harvest', ['data' => 'Crop from garden'], $item, $quantity)) throw new \Exception('Could not credit plant.');

            $plot->modifiers = null;
            $plot->started_at = null;
            $plot->water_at = null;
            $plot->waterings = null;
            $plot->is_dead = 0;
            $plot->time = null;
            $plot->item_id = null;
            $plot->save();

            flash('You have received: ' . $item->displayName .'!')->success();

            return $this->commitReturn(true);
        } catch(\Exception $e) {
            $this->setError('error', $e->getMessage());
        }
        return $this->rollbackReturn(false);
    }

    private function getRewardsString($rewards)
    {
        $results = "You have received: ";
        $result_elements = [];
        foreach($rewards as $assetType)
        {
            if(isset($assetType))
            {
                foreach($assetType as $asset)
                {
                    array_push($result_elements, $asset['asset']->name.(class_basename($asset['asset']) == 'Raffle' ? ' (Raffle Ticket)' : '')." x".$asset['quantity']);
                }
            }
        }
        return $results.implode(', ', $result_elements);
    }

    /***************************************************************************************************************
     * 
     * COOP | BARN RELATED
     * 
     ***************************************************************************************************************/

    public function place($user, $data)
    {
        DB::beginTransaction();

        try {
            $plot = UserGardenPlot::find($data['plotID']);
            if(!$plot) throw new \Exception("Invalid plot.");
            $animals = UserItem::where('item_id', $data['animalID'])->where('count', '>', 0)->where('user_id', $user->id)->get();
            if(!$animals) throw new \Exception("Invalid animal.");
            if(!$animals->first()->item->tags()->where('tag', $data['type'])->exists()) throw new \Exception("Invalid animal.");
            
            $check = 0;
            $animal = null;
            foreach($animals as $a) {if($a->garden_count < $a->count) {$check = 1; $animal = $a;}}
            if($check == 0) throw new \Exception("You don't have enough of this item.");

            if(!$animal) throw new \Exception("Invalid Animal.");
            $animal->garden_count++;
            $animal->save();

            $plot->item_id = $animal->item->id;
            $plot->started_at = Carbon::now();
            $plot->time = 24; // default 24 hours
            $plot->waterings = 0; // the user can water immediately, but we must set a base line to increment
            $plot->water_at = Carbon::now()->addHours($plot->time);
            $plot->save();

            return $this->commitReturn(true);
        } catch(\Exception $e) {
            $this->setError('error', $e->getMessage());
        }
        return $this->rollbackReturn(false);
    }

    public function animalClaim($user, $plot, $remove = false)
    {
        DB::beginTransaction();

        try { 
            if($plot->plot->plot_type == 'Coop') {
                $type = 'coop';
            }
            else {
                $type = 'barn';
            }
            $assets = parseAssetData($plot->item->tags->where('tag', $type)->first()->data['rewards']);
            if(!$rewards = fillUserAssets($assets, $user, $user, 'Garden Harvest', [
                'data' => 'Received rewards from the '.$type
            ])) throw new \Exception("Failed to credit items.");

            if($remove) {
                $animals = UserItem::where('item_id', $plot->item_id)->where('count', '>', 0)->where('user_id', $user->id)->get();
                
                $animal = null;
                foreach($animals as $a) {if($a->garden_count > 0) {$animal = $a;}}

                if(!$animal) throw new \Exception("Invalid Animal.");
                $animal->garden_count--;
                $animal->save();

                $plot->item_id = null;
                $plot->save();
            }

            $plot->modifiers = null;
            $plot->started_at = $remove ? null : carbon::now();
            $plot->water_at = $remove ? null : carbon::now()->addHours(24);
            $plot->waterings = 0;
            $plot->is_dead = 0;
            $plot->time = $remove ? null : 24;
            $plot->save();

            flash($this->getRewardsString($rewards))->success();

            return $this->commitReturn(true);
        } catch(\Exception $e) {
            $this->setError('error', $e->getMessage());
        }
        return $this->rollbackReturn(false);
    }
}