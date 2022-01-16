<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class ResetGardenAnimals extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'reset-garden-animals';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Removes all animals in the gardens and gives claims if they are ready.';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        // This only deals with animals that are in the garden. We dont have to consider seeds.
        $plots = \App\Models\User\GardenPlot::where('plot_type', '!=', 'Seed')->pluck('id');
        $userPlots = \App\Models\User\UserGardenPlot::whereIn('plot_id', $plots)->where('item_id', '!=', null)->get();
        $animalIds = [];
        foreach ($userPlots as $plot) {
            $animalIds[] = $plot->item_id;

            if($plot->readyToClaim) {
                $service = new \App\Services\PlotManager;
                $service->animalClaim($plot->user, $plot);
            }

            $plot->item_id = null;
            $plot->modifiers = null;
            $plot->started_at = null;
            $plot->water_at = null;
            $plot->waterings = 0;
            $plot->is_dead = 0;
            $plot->time = null;
            $plot->save();
        }
        // ensure no animal is left behind
        \App\Models\User\UserItem::whereIn('item_id', $animalIds)->where('garden_count', '>', 0)->get()->each(function($item) {
            $item->garden_count = 0;
            $item->save();
        });
    }
}
