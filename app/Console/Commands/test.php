<?php

namespace App\Console\Commands;

use Illuminate\Support\Str;
use Illuminate\Support\Carbon;
use Illuminate\Console\Command;
use App\Domains\Common\Models\Show;
use App\Domains\Trakt\Models\Trakt;
use App\Domains\Common\Models\Movie;
use App\Domains\Trakt\Jobs\MatchJob;
use Illuminate\Support\Facades\Cache;
use App\Domains\Common\Models\Episode;
use App\Domains\Common\Models\Service;
use App\Domains\Trakt\Jobs\SyncHistory;
use App\Domains\Trakt\Jobs\ProcessMovie;
use App\Domains\Trakt\Jobs\SyncTraktItem;
use App\Domains\Trakt\Jobs\ProcessEpisode;
use App\Domains\Trakt\Jobs\SyncWatchedHistory;
use App\Domains\Netflix\Jobs\ProcessNetflixMeta;
use App\Domains\Netflix\Services\NetflixService;
use App\Domains\Trakt\Jobs\SyncHistoryFromTrakt;
use App\Domains\Netflix\Jobs\ProcessNetflixMovie;
use App\Domains\Trakt\Services\TraktSearchService;
use App\Domains\Netflix\Jobs\ProcessNetflixEpisode;
use App\Domains\Netflix\Jobs\ProcessNetflixHistory;
use App\Domains\AmazonPrime\Services\AmazonPrimeService;

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


        $netflix = (new ProcessMovie(Movie::find(1771)));
        $netflix->handle();
        dd(1);

        //$netflix = (new ProcessEpisode(Episode::find(1703)));
        //$netflix->handle();
        dd(1);

        // $amazon = new AmazonPrimeService();
        // $amazon->loadHistoryItems(5);

        dd(2);

        $netflix = new SyncTraktItem(Trakt::find(2));
        $netflix->handle();

        // $service->loadHistoryItems(10);
        // dd(1);

        //$netflix = (new ProcessEpisode(Episode::find(1)));
        //$netflix->handle();
        //dd(1);

        $netflix = (new MatchJob());
        $netflix->handle();
        dd(1);

        foreach (Show::get() as $movie) {

            dump($movie);

            if (isset($movie['trakt'])) {

                $trakt = new Trakt();
                $trakt->trakt_id = isset($movie['trakt']['ids']['trakt']) ? $movie['trakt']['ids']['trakt'] : null;
                $trakt->match_type = $movie['trakt']['match-type'];
                $trakt->ids = isset($movie['trakt']['ids']) ? $movie['trakt']['ids'] : null;
                $trakt->information = isset($movie['trakt']['info']) ? $movie['trakt']['info'] : null;
                $trakt->score = isset($movie['trakt']['score']) ? intval($movie['trakt']['score']) : 0;
                $trakt->sync_id = isset($movie['trakt']['sync']['id']) ? $movie['trakt']['sync']['id'] : null;
                $trakt->watched_at = isset($movie['trakt']['sync']['watched_at']) ? Carbon::parse($movie['trakt']['sync']['watched_at']) : null;
                $trakt->status = !empty($trakt->sync_id) ? 2 : 0;

                $t = $movie->saveTrakt($trakt);
            }
        }
        dd(1);

        foreach (Episode::get() as $movie) {

            dump($movie);

            if (isset($movie['trakt'])) {

                $trakt = new Trakt();
                $trakt->trakt_id = isset($movie['trakt']['ids']['trakt']) ? $movie['trakt']['ids']['trakt'] : null;
                $trakt->match_type = $movie['trakt']['match-type'];
                $trakt->ids = isset($movie['trakt']['ids']) ? $movie['trakt']['ids'] : null;
                $trakt->information = isset($movie['trakt']['info']) ? $movie['trakt']['info'] : null;
                $trakt->score = isset($movie['trakt']['score']) ? intval($movie['trakt']['score']) : 0;
                $trakt->sync_id = isset($movie['trakt']['sync']['id']) ? $movie['trakt']['sync']['id'] : null;
                $trakt->watched_at = isset($movie['trakt']['sync']['watched_at']) ? Carbon::parse($movie['trakt']['sync']['watched_at']) : null;
                $trakt->status = !empty($trakt->sync_id) ? 2 : 0;

                $t = $movie->saveTrakt($trakt);
            }
        }
        dd(1);


        //Move all trakt data to new Model
        foreach (Movie::get() as $movie) {

            //dump($movie);

            $trakt = new Trakt();
            $trakt->trakt_id = isset($movie['trakt']['ids']['trakt']) ? $movie['trakt']['ids']['trakt'] : null;
            $trakt->match_type = $movie['trakt']['match-type'];
            $trakt->ids = isset($movie['trakt']['ids']) ? $movie['trakt']['ids'] : null;
            $trakt->information = isset($movie['trakt']['info']) ? $movie['trakt']['info'] : null;
            $trakt->score = isset($movie['trakt']['score']) ? intval($movie['trakt']['score']) : 0;
            $trakt->sync_id = isset($movie['trakt']['sync']['id']) ? $movie['trakt']['sync']['id'] : null;
            $trakt->watched_at = isset($movie['trakt']['sync']['watched_at']) ? Carbon::parse($movie['trakt']['sync']['watched_at']) : null;
            $trakt->status = !empty($trakt->sync_id) ? 2 : 0;

            $t = $movie->saveTrakt($trakt);
        }

        dd(1);


        $t = new SyncHistoryFromTrakt();
        $t->handle();
        dd(1);

        //$t = new SyncWatchedHistory();
        //$t->handle();
        //dd(1);


        //$t = new SyncHistory();
        //$t->handle();
        //dd(1);



        //$this->testMovie();
        $this->testShow();
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
        //$netflix = (new ProcessNetflixMeta());
        //$netflix = (new MatchJob());
        // $netflix = (new ProcessMovie(Movie::find(6)));
        //$netflix->handle();
        dd(1);

        //$netflix = (new ProcessNetflixMovie(Movie::find(3)));
        //$netflix->handle();
        //dd(1);
    }

    private function testShow()
    {
        //$netflix = (new ProcessEpisode(Episode::find(4)));
        //$netflix->handle();

        $netflix = (new ProcessNetflixEpisode(Episode::find(315)));
        $netflix->handle();
        dd(1);
    }
}
