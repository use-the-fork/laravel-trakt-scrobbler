<?php

	namespace App\Domains\Trakt\Jobs;

	use App\Domains\Common\Models\Episode;
	use App\Domains\Common\Models\Movie;
	use App\Domains\Trakt\Services\TraktSearchService;
	use Illuminate\Bus\Queueable;
	use Illuminate\Contracts\Queue\ShouldQueue;
	use Illuminate\Foundation\Bus\Dispatchable;
	use Illuminate\Queue\InteractsWithQueue;
	use Illuminate\Queue\SerializesModels;

	class ProcessEpisode implements ShouldQueue
	{
		use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

		public $episode;
		public $show;

		/**
		 * Create a new job instance.
		 *
		 * @return void
		 */
		public function __construct(Episode $episode)
		{
			$this->episode = $episode;
			$this->show = $episode->show;
		}

		/**
		 * Execute the job.
		 *
		 * @return void
		 */
		public function handle()
		{

			$traktSearchService = (new TraktSearchService());
			$matches = $traktSearchService->search('show', $this->episode->show);

			if (
				count($matches) === 1
			) {
				$this->appendShow($matches[0]);
				$this->show->refresh();
			} else {
				foreach ($matches as $match) {
					if ($traktSearchService->compareMatch('show', $this->episode, $match)) {
						$this->appendShow($match);
						$this->show->refresh();
					};
				}
			}

			if(
				!empty($this->show->trakt_id)
			){
				$match = $traktSearchService->searchEpisode($this->show->trakt_id, $this->episode->season, $this->episode->number);
				if(
					!empty($match)
				){
					$this->appendEpisode($match);
				}
			}

		}

		private function appendEpisode($match)
		{

			if (isset($match['ids']['trakt'])) {
				$this->episode->trakt_id = $match['ids']['trakt'];
			}

			if (isset($match['ids']['tmdb'])) {
				$this->episode->tmdb_id = $match['ids']['tmdb'];
			}

			$this->episode->save();
		}

		private function appendShow($match)
		{

			if (isset($match['show']['year'])) {
				$this->show->year = $match['show']['year'];
			}

			if (isset($match['show']['ids']['trakt'])) {
				$this->show->trakt_id = $match['show']['ids']['trakt'];
			}

			if (isset($match['show']['ids']['tmdb'])) {
				$this->show->tmdb_id = $match['show']['ids']['tmdb'];
			}

			$this->show->save();
		}
	}
