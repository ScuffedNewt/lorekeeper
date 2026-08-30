<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void {
        Schema::table('user_ips', function (Blueprint $table) {
            //
            $table->boolean('is_known_proxy')->default(0);
            $table->boolean('is_mobile_network')->default(0);
        });

        // Older installs may contain duplicate user/IP pairs. Keep the newest row
        // before enforcing the uniqueness expected by storeIp().
        DB::table('user_ips')->select('user_id', 'ip', DB::raw('MAX(id) as keep_id'))->groupBy('user_id', 'ip')->havingRaw('COUNT(*) > 1')->orderBy('keep_id')->each(function ($duplicate) {
            $duplicateRows = DB::table('user_ips')
                ->where('user_id', $duplicate->user_id)
                ->where('ip', $duplicate->ip);

            DB::table('user_ips')->where('id', $duplicate->keep_id)->update([
                'is_user_banned' => (clone $duplicateRows)->max('is_user_banned'),
                'created_at'     => (clone $duplicateRows)->min('created_at'),
                'updated_at'     => (clone $duplicateRows)->max('updated_at'),
            ]);

            $duplicateRows->where('id', '<>', $duplicate->keep_id)->delete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {
        Schema::table('user_ips', function (Blueprint $table) {
            //
            $table->dropColumn('is_known_proxy');
            $table->dropColumn('is_mobile_network');
        });
    }
};
