<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use App\Domains\Trakt\Enums\TraktMatchType;
use Illuminate\Database\Migrations\Migration;

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
            $table->json('media')->nullable();
            $table->string('match_type', 50)->default(TraktMatchType::NONE);
        });

        Schema::table('shows', function (Blueprint $table) {
            $table->json('media')->nullable();
        });

        Schema::table('episodes', function (Blueprint $table) {
            $table->json('media')->nullable();
            $table->string('match_type', 50)->default(TraktMatchType::NONE);
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
