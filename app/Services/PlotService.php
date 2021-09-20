<?php namespace App\Services;

use App\Services\Service;

use DB;
use Config;

use App\Models\User\GardenPlot;

class PlotService extends Service
{   
    /**
     * Creates a new plot.
     *
     * @param  array                  $data
     * @param  \App\Models\User\User  $user
     * @return bool|\App\Models\Plot\Plot
     */
    public function createPlot($data, $user)
    {
        DB::beginTransaction();

        try {
            if(isset($data['free'])) {
                $data['currency_id'] = null;
                $data['plot_cost'] = null;
            } 
            elseif(!isset($data['currency_id'])) {
                $data['currency_id'] = null;
                $data['plot_cost'] = null;
                $data['free'] = 1;
            }

            $plot = GardenPlot::create($data);

            return $this->commitReturn($plot);
        } catch(\Exception $e) {
            $this->setError('error', $e->getMessage());
        }
        return $this->rollbackReturn(false);
    }

    /**
     * Updates an plot.
     *
     * @param  \App\Models\Plot\Plot  $plot
     * @param  array                  $data
     * @param  \App\Models\User\User  $user
     * @return bool|\App\Models\Plot\Plot
     */
    public function updatePlot($plot, $data, $user)
    {
        DB::beginTransaction();

        try {

            if(isset($data['free'])) {
                $data['currency_id'] = null;
                $data['plot_cost'] = null;
            } 
            elseif(!isset($data['currency_id'])) {
                $data['currency_id'] = null;
                $data['plot_cost'] = null;
                $data['free'] = 1;
            }
            
            $plot->update($data);

            return $this->commitReturn($plot);
        } catch(\Exception $e) {
            $this->setError('error', $e->getMessage());
        }
        return $this->rollbackReturn(false);
    }

    /**
     * Deletes an plot.
     *
     * @param  \App\Models\Plot\Plot  $plot
     * @return bool
     */
    public function deletePlot($plot)
    {
        DB::beginTransaction();

        try {
            // Check first if the plot is currently owned or if some other site feature uses it
            if(DB::table('user_garden_plots')->where([['plot_id', '=', $plot->id]])->exists()) throw new \Exception("At least one user currently owns this plot. Please remove the plot(s) before deleting it.");

            DB::table('user_garden_plots')->where('plot_id', $plot->id)->delete();

            $plot->delete();

            return $this->commitReturn(true);
        } catch(\Exception $e) {
            $this->setError('error', $e->getMessage());
        }
        return $this->rollbackReturn(false);
    }
}