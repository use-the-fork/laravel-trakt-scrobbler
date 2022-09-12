<?php

namespace App\Domains\Trakt\Jobs;

use App\Domains\Common\Models\Episode;
use App\Domains\Common\Models\Movie;
use App\Domains\Trakt\Services\TraktHistoryService;
use App\Domains\Trakt\Services\TraktSearchService;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SyncWatchedHistory implements ShouldQueue
{
	use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

	/**
	 * Create a new job instance.
	 *
	 * @return void
	 */
	public function __construct()
	{
	}

	/**
	 * Execute the job.
	 *
	 * @return void
	 */
	public function handle()
	{

		foreach ($this->getMovies() as $movie) {

			$traktSearchService = (new TraktHistoryService())->getHistory('movies', $movie['trakt']['trakt']);
			if(!empty($traktSearchService)){
				$traktConfig = $movie['trakt'];
				$traktConfig['sync-ids'] = collect($traktSearchService)->pluck('id')->toArray();
				$movie['trakt'] = $traktConfig;
				$movie->synced = TRUE;
				$movie->save();
			}
		}

		foreach ($this->getEpisodes() as $episode) {
			$traktSearchService = (new TraktHistoryService())->getHistory('episodes', $episode['trakt']['trakt']);
			if (!empty($traktSearchService)) {
				$traktConfig = $episode['trakt'];
				$traktConfig['sync-ids'] = collect($traktSearchService)->pluck('id')->toArray();
				$episode['trakt'] = $traktConfig;
				$episode->synced = TRUE;
				$episode->save();
			}
		}
	}

	private function getMovies()
	{
		return Movie::where('synced', 0)->whereNotNull('trakt')->where('progress', '>=', 75)->get();
	}

	private function getEpisodes()
	{
		return Episode::where('synced', 0)->whereNotNull('trakt')->where('progress', '>=', 75)->get();
	}
}
