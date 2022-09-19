<?php

namespace App\Domains\Trakt\Http\Livewire;

use Livewire\Component;
use App\Domains\Common\Models\Movie;
use App\Domains\Trakt\Enums\TraktMatchType;

class MovieStats extends Component
{


    public $stats = [
        'total' => 0,
        'synced' => 0,
        'not-synced' => 0,
        'synced-percent' => 0,
    ];

    public function mount()
    {
        $movies = Movie::all();


        $this->stats['total'] = $movies->count();
        $this->stats['synced'] = $movies->where('synced', 1)->count();
        $this->stats['not-synced'] = $movies->where('synced', 0)->count();
        $this->stats['synced-percent'] = 100 - round(($this->stats['not-synced'] / $this->stats['synced']) * 100);

        $this->stats['match-type-service'] = $movies->where('synced', 0)->where('trakt.match-type', TraktMatchType::SERVICE)->count();
        $this->stats['match-type-single'] = $movies->where('synced', 0)->where('trakt.match-type', TraktMatchType::SINGLE)->count();
        $this->stats['match-type-compare'] = $movies->where('synced', 0)->where('trakt.match-type', TraktMatchType::COMPARED)->count();
        $this->stats['match-type-none'] = $movies->where('synced', 0)->where('trakt.match-type', TraktMatchType::NONE)->count();

        $this->stats['match-type-no-meta'] = $movies->where('synced', 0)->filter(function ($value, $key) {
            return empty($value['trakt']) || !isset($value['trakt']['ids']);
        })->count();

        //dd($movies->where('synced', 1)->count());
        //dd($movies->where('trakt.')->count());
    }

    public function render()
    {
        return view('domains.trakt.livewire.movie-stats', [
            'stats' => $this->stats,
        ]);
    }
}
