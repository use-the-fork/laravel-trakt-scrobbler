<?php

namespace App\Domains\Trakt\Http\Livewire\Match;

use Livewire\Component;
use App\Domains\Common\Models\Movie;
use App\Domains\Trakt\Enums\TraktMatchType;

class Page extends Component
{


    public $type;
    public $page = 15;


    public function mount($type)
    {

        $this->type = $type;
    }

    public function render()
    {

        switch ($this->type) {
            case 'movie':
                $movies = Movie::where('trakt->match-type', TraktMatchType::COMPARED)->paginate($this->page);
                break;
        }


        return view('domains.trakt.livewire.match.page', [
            'items' => $movies
        ]);
    }
}
