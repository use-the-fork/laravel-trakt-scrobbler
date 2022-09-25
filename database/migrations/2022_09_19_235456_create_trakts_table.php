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
        Schema::create('trakts', function (Blueprint $table) {
            $table->id();
            $table->morphs('traktable');
            $table->string('trakt_id')->nullable();
            $table->string('match_type', 50);
            $table->json('ids')->nullable();
            $table->json('information')->nullable();
            $table->string('sync_id')->nullable();
            $table->unsignedBigInteger('score')->nullable();
            $table->timestamp('watched_at')->nullable();
            $table->tinyInteger('status')->default('0');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('trakts');
    }
};
