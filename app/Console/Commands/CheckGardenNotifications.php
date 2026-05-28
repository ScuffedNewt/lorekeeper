<?php

namespace App\Console\Commands;

use App\Models\Garden\UserGardenPlot;
use App\Services\Garden\GardenManager;
use Illuminate\Console\Command;

class CheckGardenNotifications extends Command {
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'check-garden-notifications';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Notify users when garden plots become ready or perish.';

    /**
     * Executes the console command.
     *
     * @return int
     */
    public function handle(GardenManager $manager) {
        UserGardenPlot::whereNotNull('started_at')->with(['user', 'plot.garden'])->chunkById(200, function ($plots) use ($manager) {
            foreach ($plots as $plot) {
                $manager->syncNotifications($plot);
            }
        });
    }
}
