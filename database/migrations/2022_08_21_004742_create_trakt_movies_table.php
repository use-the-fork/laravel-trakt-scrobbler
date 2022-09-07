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
        Schema::create('movies', function (Blueprint $table) {
			$table->id();
			$table->foreignIdFor(\App\Domains\Common\Models\Service::class);
			$table->string('item_id', 100);
			$table->string('title', 250);
			$table->year('year')->nullable();
			$table->dateTime('watched_at')->nullable();
			$table->integer('progress')->nullable();

			$table->json('trakt')->nullable();
			$table->json('service')->nullable();
			$table->boolean('synced')->default(FALSE);
			$table->date('released_at')->nullable();

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
        Schema::dropIfExists('movies');
    }
};
