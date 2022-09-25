<?php

namespace App\Domains\Trakt\Http\Livewire\Match;

use Carbon\Carbon;
use Livewire\Component;
use App\Domains\Common\Models\Show;
use App\Domains\Common\Models\Media;
use App\Domains\Common\Models\Movie;
use App\Domains\Common\Models\Episode;
use App\Domains\Trakt\Enums\TraktStatus;
use App\Domains\Trakt\Jobs\SyncTraktItem;
use App\Domains\TMDB\Services\ImageService;

class Single extends Component
{


    public $item;
    public $status;
    public $sync;

    protected $listeners = [
        'syncTrakt' => 'syncTrakt'
    ];


    public function mount($item)
    {
        $this->item = $item;
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

            $item['trakt']['info']['year'] = $this->item->information['year'];
            $item['trakt']['info']['trailer'] = $this->item->information['trailer'];
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

        $item['trakt']['title'] = $this->item->information['title'];

        $item['url'] = $this->item->traktable->getTraktURL();

        $item['media'] = [];
        $item['media']['poster'] = $this->item->traktable->getPoster();
        $item['media']['backdrop'] = $this->item->traktable->getBackdrop();

        return view('domains.trakt.livewire.match.single', [
            'single' => $item
        ]);
    }
}
