<?php

use App\Models\Element\Typing;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('typings', function (Blueprint $table) {
            $table->integer('typing_id')->unsigned()->change();
            if (!Schema::hasColumn('typings', 'element_id')) {
                $table->integer('element_id')->unsigned();
            }
        });

        $typings = DB::table('typings')->select('*')->get();

        foreach($typings as $typing) {
            $elements = json_decode($typing->element_ids);

            foreach($elements as $element) {
                // filter out typings that just have a singular null value in them
                if(isset($element)) {
                    DB::table('typings')->insert([
                        'typing_model' => $typing->typing_model,
                        'typing_id' => $typing->typing_id,
                        'element_id' => $element,
                        // needs placeholder value because the column is non-nullable and we haven't dropped it yet
                        'element_ids' => '[]' 
                    ]);
                }
            }
            DB::table('typings')->where('id', '=', $typing->id)->delete();
        }

        Schema::table('typings', function (Blueprint $table) {
            $table->dropColumn('element_ids');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('typings', function (Blueprint $table) {
            if (!Schema::hasColumn('typings', 'element_ids')) {
                $table->string('element_ids');
            }
        });

        $typingIds = DB::table('typings')->distinct()->pluck('typing_id')->toArray();

        foreach($typingIds as $id) {
            $typings = DB::table('typings')->select('*')->where('typing_id', $id)->get();

            $elements = [];
            $rowIds = [];
            foreach($typings as $typing) {
                $elements[] = $typing->element_id;
                $rowIds[] = $typing->id;
            }
            DB::table('typings')->insert([
                'typing_model' => $typing->typing_model,
                'typing_id' => $typing->typing_id,
                'element_ids' => json_encode($elements),
                // needs placeholder value because the column is non-nullable and we haven't dropped it yet
                'element_id' => 0,
            ]);
            DB::table('typings')->whereIn('id', $rowIds)->delete();
        }

        Schema::table('typings', function (Blueprint $table) {
            $table->dropColumn('element_id');
        });
    }
};
