<?php

namespace App\Console\Commands;

use App\Domains\Common\Models\Episode;
use App\Domains\Common\Models\Movie;
use App\Domains\Common\Models\Service;
use App\Domains\Netflix\Jobs\ProcessNetflixEpisode;
use App\Domains\Netflix\Jobs\ProcessNetflixHistory;
use App\Domains\Netflix\Jobs\ProcessNetflixMovie;
use App\Domains\Netflix\Services\NetflixService;
use App\Domains\Trakt\Jobs\ProcessEpisode;
use App\Domains\Trakt\Jobs\ProcessMovie;
use App\Domains\Trakt\Services\TraktSearchService;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class test extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:run';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {


		//$netflix = Service::firstOrNew([
							//			 'name' => 'trakt',
							//			 'config' => []
							//		 ]);

		//$netflix->save();

		//$t = new TraktSyncService();

		//dd($t->getHistory());

		//$netflix = (new ProcessNetflixMovie(Movie::find(7)));
		//$netflix->handle();

		$n = new ProcessNetflixHistory();
		$n->handle();

		dd(1);
		
		$netflix = (new ProcessEpisode(Episode::find(4)));
		$netflix->handle();
		dd(1);

		$netflix = (new ProcessNetflixEpisode(Episode::find(4)));
		$netflix->handle();
		dd(1);

		//$netflix = (new ProcessNetflixMovie(Movie::find(4)));
		//$netflix->handle();


		$netflix = (new NetflixService());
		$netflix->activate();
		//$z = $netflix->loadHistoryItems();
		$z = $netflix->getItem('81290894');
dd($z);
		return 1;
    }
}
