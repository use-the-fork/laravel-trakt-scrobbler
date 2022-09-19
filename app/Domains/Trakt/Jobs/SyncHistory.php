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

class SyncHistory implements ShouldQueue
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

        $request = [];


        foreach ($this->getMovies() as $movie) {
            $request['movies'][] = [
                'watched_at' => Carbon::parse($movie->watched_at)->format('c'),
                'ids' => collect($movie['trakt']['ids'])->filter()->all()
            ];
        }

        foreach ($this->getEpisodes() as $episode) {
            $request['episodes'][] = [
                'watched_at' => Carbon::parse($episode->watched_at)->format('c'),
                'ids' => collect($episode['trakt']['ids'])->filter()->all()
            ];
        }

        dd($request);

        $traktSearchService = (new TraktHistoryService())->sync($request);
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
