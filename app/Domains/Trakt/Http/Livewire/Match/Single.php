<?php

namespace App\Domains\Trakt\Http\Livewire\Match;

use Carbon\Carbon;
use Livewire\Component;
use Illuminate\Support\Str;
use App\Domains\Common\Models\Show;
use App\Domains\Trakt\Models\Trakt;
use App\Domains\Common\Models\Media;
use App\Domains\Common\Models\Movie;
use App\Domains\Common\Models\Episode;
use App\Domains\Trakt\Enums\TraktStatus;
use App\Domains\Trakt\Jobs\SyncTraktItem;
use App\Domains\TMDB\Services\ImageService;
use App\Domains\Trakt\Enums\TraktMatchType;
use App\Domains\Trakt\Services\TraktSearchService;
use App\Domains\Trakt\Services\TraktHistoryService;

class Single extends Component
{


    public $item;
    public $status;
    public $sync;
    public $newMatch;
    public $matches = [];

    protected $listeners = [
        'syncTrakt' => 'syncTrakt'
    ];


    public function mount($item)
    {
        $this->item = $item;
    }

    public function fixMatch()
    {
        $newMatch = Str::of($this->newMatch)->afterLast('trakt.tv/')->explode('/');

        if (
            get_class($this->item->traktable) === Movie::class
        ) {
            $match = (new TraktSearchService())->getMovieSummary($newMatch[1]);

            $this->item->trakt_id = $match['ids']['trakt'];
            $this->item->match_type = TraktMatchType::MANUAL;
            $this->item->ids = $match['ids'];
            $this->item->information = [
                'title' => $match['title'],
                'year' => $match['year'],
                'tagline' => $match['tagline'],
                'overview' => $match['overview'],
                'released' => $match['released'],
                'trailer' => $match['trailer'],
                'homepage' => $match['homepage'],
            ];
            $this->item->status = TraktStatus::READY_TO_SYNC;
            $this->item->save();
            $this->item->refresh();

            if ($this->item->traktable->media) {
                $this->item->traktable->delete();
            }
        } else {

            $newMatch = (new TraktSearchService())->getShowSummary($newMatch[1]);

            $this->item->traktable->show->traktable->trakt_id = $newMatch['ids']['trakt'];
            $this->item->traktable->show->traktable->match_type = TraktMatchType::MANUAL;
            $this->item->traktable->show->traktable->ids = $newMatch['ids'];
            $this->item->traktable->show->traktable->information = [
                'title' => $newMatch['title'],
                'year' => $newMatch['year'],
                'overview' => $newMatch['overview'],
                'trailer' => $newMatch['trailer'],
                'homepage' => $newMatch['homepage'],
            ];
            $this->item->traktable->show->traktable->score = 0;
            $this->item->traktable->show->traktable->status = TraktStatus::READY_TO_SYNC;
            $this->item->traktable->show->traktable->save();
            $this->item->traktable->show->traktable->refresh();

            //Fix Children
            foreach ($this->item->traktable->show->episodes as $episode) {

                $traktSearchService = (new TraktSearchService());
                $e = $traktSearchService->getEpisodeSummary($this->item->traktable->show->traktable->trakt_id, $episode->season, $episode->number);

                //if this has been synced lets unsync it
                if (
                    !empty($episode->traktable->sync_id)
                ) {
                    $r = (new TraktHistoryService())->unsync([
                        'ids' => [
                            $episode->traktable->sync_id
                        ]
                    ]);

                    $episode->traktable->sync_id = null;
                    $episode->traktable->watched_at = null;
                }

                if ($episode->media) {
                    $episode->media->delete();
                }

                if (!$episode->traktable) {
                    $trakt = new Trakt();
                    $trakt->match_type = TraktMatchType::NONE;
                    $trakt->score = 0;
                    $trakt->status = 0;

                    $episode->saveTrakt($trakt);
                    $episode->refresh();
                }

                $episode->traktable->trakt_id = $e['ids']['trakt'];
                $episode->traktable->match_type = TraktMatchType::MANUAL;
                $episode->traktable->ids = $e['ids'];
                $episode->traktable->information = ['title' => $e['title'], 'overview' => $e['overview']];
                $episode->traktable->status = TraktStatus::READY_TO_SYNC;
                $episode->traktable->save();
            }

            $this->item->refresh();

            if ($this->item->traktable->show->media) {
                $this->item->traktable->show->media->delete();
            }
            return redirect(request()->header('Referer'));
        }
    }


    public function forceSync()
    {

        $this->item->refresh();

        $this->item->status = TraktStatus::READY_TO_SYNC;
        $this->item->save();
        dispatch(new SyncTraktItem($this->item));
    }

    public function syncTrakt($minimumWatched)
    {

        $this->item->refresh();

        if (
            $this->sync &&
            $this->sync == 1 &&
            $this->item->status == TraktStatus::READY_TO_APPROVE &&
            $this->item->traktable->progress >= $minimumWatched
        ) {

            $this->item->status = TraktStatus::READY_TO_SYNC;
            $this->item->save();

            dispatch(new SyncTraktItem($this->item));
        }
    }

    public function render()
    {

        $this->status = $this->item->status;


        $item['service']['title'] = $this->item->traktable->title;
        $item['service']['name'] = $this->item->traktable->service->name;


        //Load some things diffrently for Movies
        if (
            get_class($this->item->traktable) === Movie::class
        ) {
            $item['service']['intro'] = $this->item->traktable->year;
            $item['trakt']['sub-title'] = '';
            $item['service']['sub-title'] = '';

            $item['trakt']['info']['year'] = isset($this->item->information['year']) ? $this->item->information['year'] : '';
            $item['trakt']['info']['trailer'] = isset($this->item->information['trailer']) ? $this->item->information['trailer'] : '';
        } else {
            $item['service']['intro'] =  "S" . $this->item->traktable->season . " E" . $this->item->traktable->number;
            $item['service']['sub-title'] = $this->item->traktable->show->title;
            $item['trakt']['sub-title'] = $this->item->traktable->show->traktable->information['title'];
        }

        $item['service']['watched_at'] = Carbon::parse($this->item->traktable->watched_at)->format('D M d Y, g:i:s A');

        $item['service']['progress'] = $this->item->traktable->progress;

        $item['trakt']['score'] = $this->item->score;
        $item['trakt']['match_type'] = $this->item->match_type;
        $item['trakt']['status'] = $this->item->status;

        $item['trakt']['watched_at'] = $this->item->watched_at ? 'WATCHED ON ' . Carbon::parse($this->item->watched_at)->format('D M d Y, g:i:s A') : 'Not Watched';

        $item['trakt']['title'] = isset($this->item->information['title']) ? $this->item->information['title'] : '';

        $item['url'] = $this->item->traktable->getTraktURL();

        $item['media'] = [];
        $item['media']['poster'] = $this->item->traktable->getPoster();
        $item['media']['backdrop'] = $this->item->traktable->getBackdrop();

        return view('domains.trakt.livewire.match.single', [
            'single' => $item
        ]);
    }
}
