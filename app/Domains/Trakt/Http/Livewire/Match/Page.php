<?php

namespace App\Domains\Trakt\Http\Livewire\Match;

use Livewire\Component;
use Livewire\WithPagination;
use App\Domains\Common\Models\Show;
use App\Domains\Trakt\Models\Trakt;
use App\Domains\Common\Models\Movie;
use App\Domains\Common\Models\Episode;
use Illuminate\Database\Eloquent\Builder;
use App\Domains\Trakt\Enums\TraktStatus;
use App\Domains\Trakt\Enums\TraktMatchType;

class Page extends Component
{

    //use WithPagination;
    //protected $paginationTheme = 'bootstrap';

    public $type = 'all';
    public $match_type = 'all';
    public $hideSynced = 1;
    public $pageSize = 14;
    public $limitPerPage = 10;
    public $minWatched = 75;

    protected $listeners = [
        'load-more' => 'loadMore'
    ];

    public function mount()
    {
    }


    public function loadMore()
    {
        $this->limitPerPage = $this->limitPerPage + 6;
    }

    public function render()
    {

        $items = Trakt::whereIn('status', [
            TraktStatus::READY_TO_APPROVE,
            TraktStatus::READY_TO_SYNC,
            TraktStatus::SYNCED,
            TraktStatus::ERROR,
        ]);

        if ($this->hideSynced) {
            $items = Trakt::whereIn('status', [
                TraktStatus::READY_TO_APPROVE,
                TraktStatus::ERROR,
            ]);
        }

        if ($this->type == 'movies') {
            $items = $items->where('traktable_type', Movie::class);
        } else if ($this->type == 'episodes') {
            $items = $items->where('traktable_type', Episode::class);
        } else {
            $items = $items->whereIn('traktable_type', [Movie::class, Episode::class]);
        }

        if ($this->match_type == TraktMatchType::SERVICE) {
            $items = $items->where('match_type', TraktMatchType::SERVICE);
        } else if ($this->match_type == TraktMatchType::SINGLE) {
            $items = $items->where('match_type', TraktMatchType::SINGLE);
        } else if ($this->match_type == TraktMatchType::COMPARED) {
            $items = $items->where('match_type', TraktMatchType::COMPARED);
        } else if ($this->match_type == TraktMatchType::NONE) {
            $items = $items->where('match_type', TraktMatchType::NONE);
        }

        $items = $items->whereHas('traktable', function (Builder $query) {
            $query->where('progress', '>=', $this->minWatched);
        })->paginate($this->limitPerPage);
        $this->emit('userStore');

        return view('domains.trakt.livewire.match.page', [
            'items' => $items
        ]);
    }
}
