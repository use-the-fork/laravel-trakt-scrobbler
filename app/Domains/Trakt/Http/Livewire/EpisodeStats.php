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
    ];

    public function mount()
    {
        $episode = Episode::all();


        $this->stats['total'] = $episode->count();
        $this->stats['synced'] = $episode->where('synced', 1)->count();
        $this->stats['not-synced'] = $episode->where('synced', 0)->count();
        $this->stats['synced-percent'] = 100 - round(($this->stats['not-synced'] / $this->stats['synced']) * 100);

        $this->stats['match-type-service'] = $episode->where('synced', 0)->where('trakt.match-type', TraktMatchType::SERVICE)->count();
        $this->stats['match-type-single'] = $episode->where('synced', 0)->where('trakt.match-type', TraktMatchType::SINGLE)->count();
        $this->stats['match-type-compare'] = $episode->where('synced', 0)->where('trakt.match-type', TraktMatchType::COMPARED)->count();
        $this->stats['match-type-none'] = $episode->where('synced', 0)->where('trakt.match-type', TraktMatchType::NONE)->count();
        $this->stats['match-type-no-meta'] = $episode->where('synced', 0)->where('synced', 0)->filter(function ($value, $key) {
            return empty($value['trakt']) || !isset($value['trakt']['ids']);
        })->count();

        //dd($episode->where('synced', 1)->count());
        //dd($episode->where('trakt.')->count());
    }

    public function render()
    {
        return view('domains.trakt.livewire.episode-stats', [
            'stats' => $this->stats,
        ]);
    }
}
