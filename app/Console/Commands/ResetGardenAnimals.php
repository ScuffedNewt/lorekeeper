<?php

namespace App\Console\Commands;

use App\Models\Garden\UserGardenPlot;
use App\Models\User\UserItem;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ResetGardenAnimals extends Command {
    protected $signature = 'reset-garden-animals';
    protected $description = 'Returns every animal in a garden to its owner without granting rewards.';

    public function handle() {
        DB::transaction(function () {
            UserGardenPlot::whereNotNull('started_at')->get()->filter(fn ($plot) => $plot->object?->type === 'animal')->each(function ($plot) {
                $stack = UserItem::where('id', $plot->data['user_item_id'] ?? null)->lockForUpdate()->first();
                if ($stack && $stack->garden_count > 0) {
                    $stack->garden_count--;
                    $stack->save();
                }
                $plot->update(['started_at' => null, 'data' => null]);
            });
        });
        $this->info('All garden animals were returned.');
        return self::SUCCESS;
    }
}
