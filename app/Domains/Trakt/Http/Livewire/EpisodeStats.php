<?php

namespace App\Domains\Trakt\Http\Livewire;

use App\Domains\Common\Models\Episode;
use Livewire\Component;
use App\Domains\Common\Models\Movie;
use App\Domains\Trakt\Enums\TraktMatchType;

class EpisodeStats extends Component
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

        foreach (Episode::all() as $episode) {
            $this->stats['total']++;
            $this->stats['synced'] = $this->stats['synced'] + $episode->traktable()->where('status', 3)->count();

            $this->stats['not-synced'] = $this->stats['not-synced'] + $episode->traktable()->where('status', 0)->count();

            $this->stats['match-type-service'] = $this->stats['match-type-service'] +  $episode->traktable()->where('status', 0)->where('match_type', TraktMatchType::SERVICE)->count();
            $this->stats['match-type-single'] = $this->stats['match-type-single'] +  $episode->traktable()->where('status', 0)->where('match_type', TraktMatchType::SINGLE)->count();
            $this->stats['match-type-compare'] = $this->stats['match-type-compare'] +  $episode->traktable()->where('status', 0)->where('match_type', TraktMatchType::COMPARED)->count();
            $this->stats['match-type-none'] = $this->stats['match-type-none'] +  $episode->traktable()->where('status', 0)->where('match_type', TraktMatchType::NONE)->count();
        }

        $this->stats['synced-percent'] = 100 - round(($this->stats['not-synced'] / ($this->stats['not-synced'] + $this->stats['synced'])) * 100);
    }

    public function render()
    {
        return view('domains.trakt.livewire.episode-stats', [
            'stats' => $this->stats,
        ]);
    }
}
