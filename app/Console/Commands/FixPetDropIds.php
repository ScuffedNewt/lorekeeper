<?php

namespace App\Console\Commands;

use App\Models\Pet\PetDrop;
use App\Models\User\UserPet;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Symfony\Component\Console\Helper\ProgressBar;

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
        // this uses Symfony functions directly so that it doesn't look as haywire when called via the migration
        // it still looks a little haywire but not as much
        $symfonyOutput = $this->output->getOutput();

        $user_pets = UserPet::all();
        ProgressBar::setFormatDefinition('custom', '<info>[%bar%] %current%/%max%</info> - Fixing user pets...');
        $progressBar = new ProgressBar($symfonyOutput, $user_pets->count());
        $progressBar->setFormat('custom');
        $progressBar->setMessage('');
        $progressBar->start();

        $missingDrops = 0;

        foreach ($user_pets as $user_pet) {
            if (!($user_pet->drops)) {
                // do it got drops?
                if ($user_pet->pet->hasDrops) {
                    // create drops for pets who need them
                    $user_pet->drops()->create([
                        'drop_id'         => $user_pet->pet->dropData->id,
                        'user_pet_id'     => $user_pet->id,
                        'parameters'      => $user_pet->pet->dropData->rollParameters(),
                        'drops_available' => 0,
                        'next_day'        => Carbon::now()
                            ->add($user_pet->pet->dropData->frequency, $user_pet->pet->dropData->interval)
                            ->startOf($user_pet->pet->dropData->interval),
                    ]);
                } else {
                    // create empty drops for pets that don't have them
                    $user_pet->drops()->create([
                        'drop_id'         => null,
                        'user_pet_id'     => $user_pet->id,
                        'parameters'      => null,
                        'drops_available' => 0,
                        'next_day'        => null,
                    ]);
                }
                $missingDrops++;
            } elseif (isset($user_pet->pet->dropData) && ($user_pet->drops->drop_id !== $user_pet->pet->dropData->id)) {
                // fix pets that have the wrong drop_id assigned
                $user_pet->drops->update([
                    'drop_id'         => $user_pet->pet->dropData->id,
                    'parameters'      => $user_pet->pet->dropData->rollParameters(),
                    'drops_available' => 0,
                    'next_day'        => Carbon::now()
                        ->add($user_pet->pet->dropData->frequency, $user_pet->pet->dropData->interval)
                        ->startOf($user_pet->pet->dropData->interval),
                ]);
                $symfonyOutput->writeln('<comment>Corrected pet ID #'.$user_pet->id.' (wrong drop_id)</comment>');
            } elseif (!isset($user_pet->pet->dropData) && $user_pet->drops->drop_id) {
                // fix pets that had drops that no longer do
                $user_pet->drops->update([
                    'drop_id'         => null,
                    'parameters'      => null,
                    'drops_available' => 0,
                    'next_day'        => null,
                ]);
                $symfonyOutput->writeln('<comment>Corrected pet ID #'.$user_pet->id.' (removed non-existent drops)</comment>');
            } elseif (isset($user_pet->pet->dropData)) {
                // even if everything else looks fine, the parameters can still be out of sync
                // so let's fix that
                if (!in_array($user_pet->drops->parameters, array_keys($user_pet->pet->dropData->parameters))) {
                    $user_pet->drops->update([
                        'parameters' => $user_pet->pet->dropData->rollParameters(),
                    ]);
                    $symfonyOutput->writeln('<comment>Corrected pet ID #'.$user_pet->id.' (fixed mismatched parameters)</comment>');
                }
            }
            $progressBar->advance();
        }
        $progressBar->finish();
        $symfonyOutput->writeln("\n<comment>".$missingDrops.' pets needed drop data rows created.</comment>');

        $symfonyOutput->writeln('');

        $deletedDrops = PetDrop::whereDoesntHave('user_pet')->get();
        if ($deletedDrops->count()) {
            $progressBar = new ProgressBar($symfonyOutput, $deletedDrops->count());
            $progressBar->setFormat('custom');
            $progressBar->setMessage('Deleting invalid drops...');
            $progressBar->start();
            foreach ($deletedDrops as $deletedDrop) {
                // just a double check
                if ($deletedDrop->user_pet !== null) {
                    // the pet is deleted and the PetDrop is no longer needed
                    $symfonyOutput->writeln('<comment>Deleted drop data for deleted pet #'.$deletedDrop->user_pet_id.'</comment>');
                    $deletedDrop->delete();
                }
                $progressBar->advance();
            }
            $progressBar->finish();
        } else {
            $symfonyOutput->write('No invalid drops to delete.');
        }

        return Command::SUCCESS;
    }
}
