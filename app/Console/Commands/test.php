<?php

namespace App\Console\Commands;

use Illuminate\Support\Str;
use Illuminate\Console\Command;
use App\Domains\Common\Models\Movie;
use Illuminate\Support\Facades\Cache;
use App\Domains\Common\Models\Episode;
use App\Domains\Common\Models\Service;
use App\Domains\Trakt\Jobs\SyncHistory;
use App\Domains\Trakt\Jobs\ProcessMovie;
use App\Domains\Trakt\Jobs\ProcessEpisode;
use App\Domains\Trakt\Jobs\SyncWatchedHistory;
use App\Domains\Netflix\Services\NetflixService;
use App\Domains\Netflix\Jobs\ProcessNetflixMovie;
use App\Domains\Trakt\Services\TraktSearchService;
use App\Domains\Netflix\Jobs\ProcessNetflixEpisode;
use App\Domains\Netflix\Jobs\ProcessNetflixHistory;

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

		//$t = new SyncWatchedHistory();
		//$t->handle();
		//dd(1);


		//$t = new SyncHistory();
		//$t->handle();
		//dd(1);


		$this->testMovie();
		dd(1);


		//$netflix = Service::firstOrNew([
		//			 'name' => 'trakt',
		//			 'config' => []
		//		 ]);

		//$netflix->save();


		$netflix = (new ProcessEpisode(Episode::find(4)));
		$netflix->handle();
		dd(1);



		//dd($t->getHistory());

		//$netflix = (new ProcessNetflixMovie(Movie::find(7)));
		//$netflix->handle();

		$n = new ProcessNetflixHistory();
		$n->handle();

		dd(1);

		$netflix = (new ProcessNetflixMovie(Movie::find(4)));
		$netflix->handle();


		$netflix = (new NetflixService());
		$netflix->activate();
		//$z = $netflix->loadHistoryItems();
		$z = $netflix->getItem('81290894');
		dd($z);
		return 1;
	}


	private function testMovie()
	{
		$netflix = (new ProcessMovie(Movie::find(3)));
		$netflix->handle();
		dd(1);

		$netflix = (new ProcessNetflixMovie(Movie::find(3)));
		$netflix->handle();
		dd(1);
	}

	private function testShow()
	{
		$netflix = (new ProcessEpisode(Episode::find(4)));
		$netflix->handle();

		//$netflix = (new ProcessNetflixEpisode(Episode::find(4)));
		//$netflix->handle();
		dd(1);
	}
}
