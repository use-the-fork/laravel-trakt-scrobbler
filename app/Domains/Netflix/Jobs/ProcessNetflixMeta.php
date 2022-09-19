<?php

namespace App\Domains\Netflix\Jobs;

use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use App\Domains\Common\Models\Movie;
use App\Domains\Common\Models\Episode;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use App\Domains\Netflix\Services\NetflixService;

class ProcessNetflixMeta implements ShouldQueue
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
        foreach ($this->getEpisodes() as $episode) {
            dispatch(new ProcessNetflixEpisode($episode));
        }
    }


    private function getEpisodes()
    {
        return Episode::where('synced', 0)->whereNull('number')->get();
    }
}
