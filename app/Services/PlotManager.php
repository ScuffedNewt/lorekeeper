<?php namespace App\Services;

use App\Services\Service;

use DB;
use Config;

use App\Models\User\GardenPlot;
use App\Models\Currency\Currency;
use App\Models\User\UserGardenPlot;
use App\Services\CurrencyManager;

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
}