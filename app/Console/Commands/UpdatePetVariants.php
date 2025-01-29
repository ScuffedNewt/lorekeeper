<?php

namespace App\Console\Commands;

use App\Models\Pet\Pet;
use App\Models\Pet\PetDropData;
use App\Models\Pet\PetEvolution;
use App\Models\User\UserPet;
use Illuminate\Console\Command;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class UpdatePetVariants extends Command {
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'update-pet-variants';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Updates pet variants to be just pets with a parent_id';

    /**
     * Execute the console command.
     */
    public function handle() {
        // image = $this->pet_id.'-variant-'.$this->id.'-image.png';

        if (!Schema::hasTable('pet_variants')) {
            $this->info('No pet variants to update');

            return;
        }

        $path = public_path('images/data/pets');

        $variants = DB::table('pet_variants')->get();

        foreach ($variants as $variant) {
            $parentPet = Pet::find($variant->pet_id);
            $pet = Pet::create([
                'name'               => $variant->variant_name,
                'pet_category_id'    => $parentPet->pet_category_id,
                'parent_id'          => $variant->pet_id,
                'has_image'          => 1,
                'description'        => $variant->description,
                'parsed_description' => parse($variant->description),
                'allow_transfer'     => $parentPet->allow_transfer,
                'limit'              => $parentPet->limit,
            ]);

            // move variant image to pet image
            $oldImage = $path.'/'.$variant->pet_id.'-variant-'.$variant->id.'-image.png';
            $newImage = $path.'/'.$pet->id.'-image.png';

            if (file_exists($oldImage)) {
                rename($oldImage, $newImage);
            }

            // evolutions
            $petEvolutions = PetEvolution::where('pet_id', $variant->pet_id)->get();
            foreach ($petEvolutions as $evolution) {
                $oldImage = $path.'/evolutions/'.$variant->pet_id.'-evolution-'.$evolution->id.'-variant-'.$variant->id.'-image.png';
                if (file_exists($oldImage)) {
                    // move evolution variant image to pet evolution image
                    $newEvolution = PetEvolution::create([
                        'pet_id'          => $pet->id,
                        'evolution_name'  => $evolution->evolution_name.' - '.$variant->variant_name,
                        'evolution_stage' => $evolution->evolution_stage,
                    ]);

                    $newImage = $path.'/evolutions/'.$newEvolution->imageFileName;

                    rename($oldImage, $newImage);
                }
            }

            // user pets
            $userVariantPets = UserPet::where('variant_id', $variant->id)->get();
            foreach ($userVariantPets as $userPet) {
                $userPet->update([
                    'pet_id' => $pet->id,
                ]);
            }

            $variantDropData = DB::table('pet_variant_drop_data')->where('variant_id', $variant->id)->first();
            $parentPetDropData = PetDropData::where('pet_id', $variant->pet_id)->first();
            if (!$variantDropData || !$parentPetDropData) {
                continue;
            }
            $dropData = PetDropData::create([
                'pet_id'     => $pet->id,
                'parameters' => $parentPetDropData->parameters,
                'is_active'  => $parentPetDropData->is_active,
                'name'       => $parentPetDropData->name.' - '.$variant->variant_name,
                'frequency'  => $parentPetDropData->frequency,
                'interval'   => $parentPetDropData->interval,
                'cap'        => $parentPetDropData->cap,
                //
                'data' => json_decode($variantDropData->data),
            ]);
        }

        Schema::dropIfExists('pet_variants');
        Schema::dropIfExists('pet_variant_drop_data');
        Schema::table('pet_evolutions', function (Blueprint $table) {
            $table->dropColumn('variants');
        });
        Schema::table('user_pets', function (Blueprint $table) {
            $table->dropColumn('variant_id');
        });
    }
}
