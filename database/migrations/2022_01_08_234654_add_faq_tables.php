<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFaqTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        Schema::create('faq_categories', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
        });

        Schema::create('faq_tags', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
        });

        Schema::create('faq_questions', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('category_id')->nullable()->default(null);
            $table->text('question');
            $table->text('answer');
            $table->string('status')->default('answered');
            $table->timestamps();
        });

        Schema::create('faq_question_tags', function (Blueprint $table) {
            $table->integer('question_id')->unsigned();
            $table->integer('tag_id')->unsigned();
            $table->primary(['question_id', 'tag_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
        Schema::dropIfExists('faq_categories');
        Schema::dropIfExists('faq_tags');
        Schema::dropIfExists('faq_questions');
        Schema::dropIfExists('faq_question_tags');
    }
}
