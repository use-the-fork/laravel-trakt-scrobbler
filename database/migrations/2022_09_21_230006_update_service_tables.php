<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('movies', function (Blueprint $table) {
            $table->dropColumn('media');
            $table->dropColumn('synced');
            $table->dropColumn('match_type');
            $table->dropColumn('service');
            $table->dropColumn('trakt');
        });

        Schema::table('shows', function (Blueprint $table) {
            $table->dropColumn('media');
            $table->dropColumn('synced');
            $table->dropColumn('match_type');
            $table->dropColumn('service');
            $table->dropColumn('trakt');
            $table->dropColumn('watched_at');
        });

        Schema::table('episodes', function (Blueprint $table) {
            $table->dropColumn('media');
            $table->dropColumn('synced');
            $table->dropColumn('match_type');
            $table->dropColumn('service');
            $table->dropColumn('trakt');
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
    }
};
