<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\TransferRequest;
use App\Models\User\UserItem;
use App\Models\Currency\Currency;

use App\Services\InventoryManager;
use App\Services\CurrencyManager;

class FixTransferRequests extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fix-transfer-requests';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fixes all approved transfer requests.';

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
     * @return mixed
     */
    public function handle()
    {
        //
        $transfers = TransferRequest::where('status', 'Approved')->get();
        foreach($transfers as $transfer)
        {

            $items = json_decode($transfer->items);
            //////
            if(isset($items->stack_id[0])) {

            $service = new InventoryManager;

            foreach($items->stack_id as $key => $item) {
                $userItem = UserItem::find($item);
                $service->moveStack($t->user, $t->recipient, 'User Transfer', ['data' => 'Transferred by ' . $t->user->displayname], $userItem, $items->quantity[$key])

                $userItem->transfer_count -= $items->quantity[$key];
                $userItem->save();
                    
                }
            }
            ////
            elseif(isset($items->currency_id[0])) {
                $service = new CurrencyManager;

                $quantity = 0;
                foreach($items->quantity as $q) $quantity += $q;

                $service->creditCurrency($t->user, $t->recipient, 'User Transfer', null, Currency::find($items->currency_id[0]), $quantity)) 
            }
        }
    }
}
