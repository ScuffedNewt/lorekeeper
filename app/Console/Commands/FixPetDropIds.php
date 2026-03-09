<?php

namespace App\Console\Commands;

use App\Models\Pet\PetDrop;
use Illuminate\Console\Command;

class FixPetDropIds extends Command {
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fix-pet-drop-ids';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fixes inconsistent pet drop data IDs';

    /**
     * Execute the console command.
     */
    public function handle() {
        $drops = PetDrop::all();
        foreach ($drops as $drop) {
            if ($drop->user_pet !== null) {
                if (isset($drop->user_pet->pet->dropData) && ($drop->drop_id !== $drop->user_pet->pet->dropData->id)) {
                    $drop->update([
                        'drop_id' => $drop->user_pet->pet->dropData->id,
                    ]);
                    $this->line("Corrected pet ID #".$drop->user_pet->id."\n");
                } elseif(!isset($drop->user_pet->pet->dropData)) {
                    // the pet has no drop data and the PetDrop can be deleted
                    $drop->delete();
                    $this->line("Deleted drop data for pet #".$drop->user_pet->id." that has no drops\n");
                }
            } else {
                // the pet is deleted and the PetDrop is no longer needed
                $drop->delete();
                $this->line("Deleted drop data for deleted pet #".$drop->user_pet->id."\n");
            }
        }
    }
}
