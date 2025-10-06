<?php

namespace App\Console\Commands;

use File;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class UpdatePets extends Command {
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'update-pets';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Updates pets. Converts old format drop data and adds variant_data column to pet_drop_data table.';

    /**
     * Create a new command instance.
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle() {
        // add new col
        // check if col exists
        if (Schema::hasTable('pet_variant_drop_data')) {
            $this->info('Already updated tables, updating data.');
        } else {
            Schema::table('pet_drop_data', function ($table) {
                $table->string('name')->default('drop');
                $table->integer('frequency');
                $table->string('interval')->default('Hour');
                $table->integer('cap')->default(null)->nullable();
                $table->boolean('override')->default(false);
            });
            Schema::table('pet_categories', function ($table) {
                $table->integer('limit')->default(null)->nullable();
            });
            Schema::table('pets', function ($table) {
                $table->integer('limit')->default(null)->nullable();
            });
            Schema::create('pet_variant_drop_data', function ($table) {
                $table->increments('id');
                $table->integer('variant_id')->unsigned();
                $table->json('data')->default(null)->nullable();
            });
            $this->info('Updated pet drop data table.');
        }

        // convert old data
        $drop_data = \App\Models\Pet\PetDropData::all();
        foreach ($drop_data as $drop) {
            // check if 'assets' offset exists on $drop->data, if it does continue
            if (isset($drop->data['assets'])) {
                $this->line('Skipping drop data for pet: '.$drop->pet->name.'...');
            } else {
                $this->line('Converting drop data for pet: '.$drop->pet->name.'...');
                $drop->name = $drop->data['drop_name'];
                $drop->frequency = $drop->data['frequency']['frequency'];
                $drop->interval = $drop->data['frequency']['interval'];
                $drop->cap = $drop->data['cap'];
                $this->convertItems($drop, $drop->data['items']);
                $this->info('Converted drop data for pet: '.$drop->pet->name.'.');
            }
        }

        // update variant images to use ID instead of name
        if (class_exists('\App\Models\Pet\PetVariant')) {
            $variants = \App\Models\Pet\PetVariant::all();
            foreach ($variants as $variant) {
                $this->line('Updating variant image for variant: '.$variant->variant_name.'...');
                // rename image
                $old_image = $variant->imageDirectory.'/'.$variant->pet_id.'-'.$variant->variant_name.'-image.png';
                // rename
                if (File::exists(public_path($old_image))) {
                    $new_image = $variant->imageDirectory.'/'.$variant->pet_id.'-variant-'.$variant->id.'-image.png';
                    File::move(public_path($old_image), public_path($new_image));
                }
            }
        } else {
            $variants = DB::table('pet_variants')->get();
            // need to manually define the image directory
            foreach ($variants as $variant) {
                $this->line('Updating variant image for variant: '.$variant->variant_name.'...');
                // rename image
                $old_image = 'images/data/pets/'.$variant->pet_id.'-'.$variant->variant_name.'-image.png';
                // rename
                if (File::exists(public_path($old_image))) {
                    $new_image = 'images/data/pets/'.$variant->pet_id.'-variant-'.$variant->id.'-image.png';
                    File::move(public_path($old_image), public_path($new_image));
                }
            }
        }
    }

    private function convertItems($drop, $data) {
        foreach ($data as $key => $group) {
            $this->line('Converting group: '.$key.'...');
            // if it's the base pet, put it on the data table
            if ($key == 'pet') {
                $assets = [];
                foreach ($group as $name => $item) {
                    $assets[strtolower($name)]['items'][$item['item_id']] = [
                        'min_quantity' => $item['min'],
                        'max_quantity' => $item['max'],
                    ];
                }
                $drop->data = ['assets' => $assets];
            } else {
                $assets = [];
                foreach ($group as $name => $item) {
                    $assets[strtolower($name)]['items'][$item['item_id']] = [
                        'min_quantity' => $item['min'],
                        'max_quantity' => $item['max'],
                    ];
                }
                // if not "pet" we create a PetVariantDropData entry with the data
                \App\Models\Pet\PetVariantDropData::create([
                    'variant_id' => $key,
                    'data'       => json_encode(['assets' => $assets]),
                ]);
            }
        }
        $drop->save();
    }
}
