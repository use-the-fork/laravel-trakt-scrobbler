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

class SyncHistoryFromTrakt implements ShouldQueue
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

        $traktSearchService = (new TraktHistoryService())->getHistory();

        foreach ($traktSearchService as $history) {
            switch ($history['type']) {
                case 'episode':

                    $e = Episode::where('trakt->ids->trakt', $history['episode']['ids']['trakt'])->first();
                    if ($e) {
                        $trakt = $e->trakt;
                        $trakt['sync'] = [
                            'id' => $history['id'],
                            'watched_at' => $history['watched_at'],
                            'action' => $history['action'],
                            'type' => $history['type'],
                        ];
                        $e->trakt = $trakt;
                        $e->synced = TRUE;
                        $e->save();
                    }

                    break;
                case 'movie':
                    $e = Movie::where('trakt->ids->trakt', $history['movie']['ids']['trakt'])->first();

                    if ($e) {
                        $trakt = $e->trakt;
                        $trakt['sync'] = [
                            'id' => $history['id'],
                            'watched_at' => $history['watched_at'],
                            'action' => $history['action'],
                            'type' => $history['type'],
                        ];
                        $e->trakt = $trakt;
                        $e->synced = TRUE;
                        $e->save();
                    }
                    break;
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
