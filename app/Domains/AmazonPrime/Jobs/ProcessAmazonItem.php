<?php

namespace App\Domains\AmazonPrime\Jobs;

use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use App\Domains\Common\Models\Episode;
use App\Domains\Common\Models\Service;
use Illuminate\Queue\SerializesModels;
use App\Domains\Trakt\Jobs\ProcessMovie;
use Illuminate\Queue\InteractsWithQueue;
use App\Domains\Trakt\Jobs\ProcessEpisode;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use App\Domains\Netflix\Services\NetflixService;
use App\Domains\AmazonPrime\Services\AmazonPrimeService;

class ProcessAmazonItem implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $item;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($item)
    {
        $this->item = $item;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {

        $service = new AmazonPrimeService();
        $item = $service->getItem($this->item);



        $service->__destruct();
    }
}
