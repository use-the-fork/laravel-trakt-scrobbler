<?php

namespace App\Domains\Trakt\Jobs;

use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use App\Domains\Trakt\Models\Trakt;
use App\Domains\Common\Models\Movie;
use App\Domains\Common\Models\Episode;
use Illuminate\Queue\SerializesModels;
use App\Domains\Trakt\Enums\TraktStatus;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use App\Domains\Trakt\Services\TraktSearchService;
use App\Domains\Trakt\Services\TraktHistoryService;

class SyncTraktItem implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;


    protected $trakt;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(Trakt $trakt)
    {
        $this->trakt = $trakt;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {

        //lets see if we already tracked this
        if (
            $this->trakt->status == TraktStatus::SYNCED ||
            $this->trakt->status == TraktStatus::ERROR
        ) {
            return;
        }

        $traktSearchService = new TraktHistoryService();

        //Check if we already have this in our history
        $traktItemHistory = $traktSearchService->getHistory($this->trakt->traktable->getTraktType(), $this->trakt->trakt_id);
        if (
            isset($traktItemHistory[0]) &&
            !empty($traktItemHistory[0])
        ) {
            $this->trakt->sync_id = $traktItemHistory[0]['id'];
            $this->trakt->watched_at = Carbon::parse($traktItemHistory[0]['watched_at']);
            $this->trakt->status = TraktStatus::SYNCED;
            $this->trakt->save();
        } else {
            //Need to sync
            $request[$this->trakt->traktable->getTraktType()][] = [
                'watched_at' => Carbon::parse($this->trakt->traktable->watched_at)->format('c'),
                'ids' => collect($this->trakt['ids'])->filter()->all()
            ];
            $traktSearchService->sync($request);

            $traktItemHistory = $traktSearchService->getHistory($this->trakt->traktable->getTraktType(), $this->trakt->trakt_id);
            if (
                isset($traktItemHistory[0]) &&
                !empty($traktItemHistory[0])
            ) {
                $this->trakt->sync_id = $traktItemHistory[0]['id'];
                $this->trakt->watched_at = Carbon::parse($traktItemHistory[0]['watched_at']);
                $this->trakt->status = TraktStatus::SYNCED;
                $this->trakt->save();
            } else {
                $this->trakt->status = TraktStatus::ERROR;
                $this->trakt->save();
            }
        }
    }
}
