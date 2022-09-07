<?php

namespace App\Domains\Netflix\Jobs;

use App\Domains\Common\Models\Movie;
use App\Domains\Netflix\Services\NetflixService;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessNetflixMovie implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

	public $movie;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(Movie $movie)
    {
		$this->movie = $movie;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $service = new NetflixService();
		$item = $service->getItem($this->movie->service_id);

		if(
			!isset($item->video)
		){
			return;
		}

		//Update the item with the meta data
		$this->movie->year = $item->video->year;
		$this->movie->release_date = Carbon::parse($item->video->start / 1000);
		$this->movie->save();
    }
}
