<?php

namespace App\Domains\Netflix\Jobs;

use App\Domains\Common\Models\Episode;
use App\Domains\Common\Models\Service;
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
        $this->onQueue('service');
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {

        $this->episode->refresh();
        if (
            !empty($this->episode->season) &&
            !empty($this->episode->number)
        ) {
            //we dont need to process items that already have a season or number
            return;
        }

        $service = new NetflixService();
        $item = $service->getItem($this->episode->item_id);

        if (
            !isset($item->video)
        ) {
            return;
        }

        foreach ($item->video->seasons as $season) {
            foreach ($season->episodes as $episode) {
                if (
                    $episode->episodeId == $this->episode->item_id
                ) {

                    $this->episode->year = $season->year;
                    $this->episode->season = $season->seq;
                    $this->episode->released_at = Carbon::parse($service->convertUnixDate($episode->start));
                    $this->episode->number = $episode->seq;
                    $this->episode->show->year = $season->year;
                    $this->episode->save();
                    $this->episode->show->save();
                } else if (
                    // Since we are here lets see if any of these other episodes match and can be updated.
                    $otherEpisode = Service::where('name', 'netflix')->first()->episodes()->where('service_id', 1)->where('item_id', $episode->episodeId)->first()
                ) {

                    $otherEpisode->year = $season->year;
                    $otherEpisode->season = $season->seq;
                    $otherEpisode->released_at = Carbon::parse($service->convertUnixDate($episode->start));
                    $otherEpisode->number = $episode->seq;
                    $otherEpisode->show->year = $season->year;
                    $otherEpisode->save();
                    $otherEpisode->show->save();
                }
            }
        }


        $service->__destruct();
    }
}
