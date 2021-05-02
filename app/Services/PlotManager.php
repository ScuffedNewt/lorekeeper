<?php namespace App\Services;

use App\Services\Service;

use DB;
use Config;
use Carbon\Carbon;

use App\Models\User\GardenPlot;
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
            
            if($plot->free)
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

            $seed = UserItem::find($data['seedID']);
            if(!$seed) throw new \Exception("Invalid seed.");

            $invman = new InventoryManager;
            if(!$invman->debitStack($user, null, ['data' => 'Seed planted in garden.'], $seed, 1)) throw new \Exception('Could not debit seed.');

            $plot->item_id = $seed->item->id;
            $plot->started_at = Carbon::now();
            // this is where we set the plot time between each waterings
            // because this is a function we can add things later
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

    /**
     * 
     */
    public function water($user, $data)
    {
        DB::beginTransaction();

        try { 
            
            $plot = UserGardenPlot::find($data['plotID']);
            if(!$plot) throw new \Exception("Invalid plot.");

            $plot->waterings += 1;
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
    public function clear($user, $data)
    {
        DB::beginTransaction();

        try { 

            $plot = UserGardenPlot::find($data);
            if(!$plot) throw new \Exception("Invalid plot.");

            $plot->modifiers = null;
            $plot->started_at = null;
            $plot->water_at = null;
            $plot->waterings = null;
            $plot->is_dead = 0;
            $plot->time = null;
            $plot->save();

            return $this->commitReturn(true);
        } catch(\Exception $e) {
            $this->setError('error', $e->getMessage());
        }
        return $this->rollbackReturn(false);
    }
}