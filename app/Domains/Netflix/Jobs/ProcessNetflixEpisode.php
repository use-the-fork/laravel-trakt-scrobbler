<?php

	namespace App\Domains\Netflix\Jobs;

	use App\Domains\Common\Models\Episode;
	use App\Domains\Netflix\Services\NetflixService;
	use Carbon\Carbon;
	use Illuminate\Bus\Queueable;
	use Illuminate\Contracts\Queue\ShouldQueue;
	use Illuminate\Foundation\Bus\Dispatchable;
	use Illuminate\Queue\InteractsWithQueue;
	use Illuminate\Queue\SerializesModels;

	class ProcessNetflixEpisode implements ShouldQueue
	{
		use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

		public $episode;

		/**
		 * Create a new job instance.
		 *
		 * @return void
		 */
		public function __construct(Episode $episode)
		{
			$this->episode = $episode;
		}

		/**
		 * Execute the job.
		 *
		 * @return void
		 */
		public function handle()
		{
			$service = new NetflixService();
			$item = $service->getItem($this->episode->service_id);

			if (
				!isset($item->video)
			) {
				return;
			}

			foreach ($item->video->seasons as $season) {
				foreach ($season->episodes as $episode) {
					if (
						$episode->episodeId == $this->episode->service_id
					) {

						$this->episode->year = $season->year;
						$this->episode->season = $season->seq;
						$this->episode->release_date = Carbon::parse($episode->start / 1000);
						$this->episode->number = $episode->seq;
						$this->episode->show->year = $season->year;
						$this->episode->save();
						$this->episode->show->save();
					} else if(
						// Since we are here lets see if any of these other episodes match and can be updated.
						$otherEpisode = Episode::where('service', 'netflix')->where('service_id', $episode->episodeId)->first()
					){
						$otherEpisode->year = $season->year;
						$otherEpisode->season = $season->seq;
						$otherEpisode->release_date = Carbon::parse($episode->start / 1000);
						$otherEpisode->number = $episode->seq;
						$otherEpisode->show->year = $season->year;
						$otherEpisode->save();
						$otherEpisode->show->save();
					}
				}
			}

		}
	}
