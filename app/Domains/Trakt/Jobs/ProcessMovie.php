<?php

	namespace App\Domains\Trakt\Jobs;

	use App\Domains\Common\Models\Movie;
	use App\Domains\Trakt\Services\TraktSearchService;
	use Illuminate\Bus\Queueable;
	use Illuminate\Contracts\Queue\ShouldQueue;
	use Illuminate\Foundation\Bus\Dispatchable;
	use Illuminate\Queue\InteractsWithQueue;
	use Illuminate\Queue\SerializesModels;

	class ProcessMovie implements ShouldQueue
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
			$traktSearchService = (new TraktSearchService());
			$matches = $traktSearchService->search('movie', $this->movie);
			if (
				count($matches) === 1
			) {
				$this->append($matches[0]);
			} else {
				foreach ($matches as $match) {
					if ($traktSearchService->compareMatch('movie', $this->movie, $match)) {
						$this->append($match);
					};
				}
			}
		}

		private function append($match)
		{
			$this->movie->trakt = $match['movie']['ids'];
			$this->movie->save();

		}
	}
