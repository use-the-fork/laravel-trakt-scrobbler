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
        'match-type-service' => 0,
        'match-type-single' => 0,
        'match-type-compare' => 0,
        'match-type-none' => 0,
    ];

    public function mount()
    {
        foreach (Movie::all() as $movie) {
            $this->stats['total']++;
            $this->stats['synced'] = $this->stats['synced'] + $movie->traktable()->where('status', 3)->count();

            $this->stats['not-synced'] = $this->stats['not-synced'] + $movie->traktable()->where('status', 0)->count();

            $this->stats['match-type-service'] = $this->stats['match-type-service'] +  $movie->traktable()->where('status', 0)->where('match_type', TraktMatchType::SERVICE)->count();
            $this->stats['match-type-single'] = $this->stats['match-type-single'] +  $movie->traktable()->where('status', 0)->where('match_type', TraktMatchType::SINGLE)->count();
            $this->stats['match-type-compare'] = $this->stats['match-type-compare'] +  $movie->traktable()->where('status', 0)->where('match_type', TraktMatchType::COMPARED)->count();
            $this->stats['match-type-none'] = $this->stats['match-type-none'] +  $movie->traktable()->where('status', 0)->where('match_type', TraktMatchType::NONE)->count();
        }

        $this->stats['synced-percent'] = 100 - round(($this->stats['not-synced'] / ($this->stats['not-synced'] + $this->stats['synced'])) * 100);
    }

    public function render()
    {
        return view('domains.trakt.livewire.movie-stats', [
            'stats' => $this->stats,
        ]);
    }
}
