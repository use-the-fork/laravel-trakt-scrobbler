<?php

namespace App\Domains\Trakt\Jobs;

use App\Domains\Common\Models\Episode;
use App\Domains\Common\Models\Movie;
use App\Domains\Trakt\Models\Trakt;
use App\Domains\Trakt\Services\TraktHistoryService;
use App\Domains\Trakt\Services\TraktSearchService;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class MatchJob implements ShouldQueue
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

        foreach ($this->getMovies() as $count => $movie) {
            dispatch(new ProcessMovie($movie));
        }

        foreach ($this->getEpisodes() as $count => $episode) {
            dispatch(new ProcessEpisode($episode));
        }
    }

    private function getMovies()
    {
        return Movie::get()->filter(function ($value, $key) {
            return !isset($value->traktable->exists) || empty($value->traktable->exists) || empty($value->traktable);
        })->all();
    }

    private function getEpisodes()
    {
        return Episode::whereNotNull('number')->get()->filter(function ($value, $key) {
            return !isset($value->traktable->exists) || empty($value->traktable->exists) || empty($value->traktable);
        })->all();
    }
}
