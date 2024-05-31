<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTraitSubcategories extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('feature_subcategories', function(Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->increments('id');

            $table->string('name');
            $table->text('description')->nullable()->default(null);
            $table->text('parsed_description')->nullable()->default(null);
            $table->boolean('has_image')->default(0);
            $table->integer('sort')->unsigned()->default(0);
        });

        Schema::table('features', function(Blueprint $table) {
            $table->integer('feature_subcategory_id')->unsigned()->nullable()->default(null);
            $table->foreign('feature_subcategory_id')->references('id')->on('feature_subcategories');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('features', function(Blueprint $table) {
            $table->dropForeign('features_feature_subcategory_id_foreign');
            $table->dropColumn('feature_subcategory_id');
        });
        Schema::dropIfExists('feature_subcategories');
    }
}
