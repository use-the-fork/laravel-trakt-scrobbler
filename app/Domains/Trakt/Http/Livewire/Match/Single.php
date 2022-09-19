<?php

namespace App\Domains\Trakt\Http\Livewire\Match;

use Carbon\Carbon;
use Livewire\Component;
use App\Domains\Common\Models\Movie;
use App\Domains\Trakt\Enums\TraktMatchType;
use App\Domains\TMDB\Services\ImageService;

class Single extends Component
{


    public $item;


    public function mount($item)
    {

        $this->item = $item;
    }

    public function render()
    {


        $item['title'] = $this->item->title;
        $item['watched_at'] = Carbon::parse($this->item->watched_at)->format('Y-m-d H:i:s');

        $item['trakt']['score'] = $this->item['trakt']['score'];
        $item['trakt']['match_type'] = $this->item['trakt']['match-type'];
        $item['trakt']['info']['title'] = $this->item['trakt']['info']['title'];
        $item['trakt']['info']['year'] = $this->item['trakt']['info']['year'];
        $item['trakt']['info']['trailer'] = $this->item['trakt']['info']['trailer'];

        $item['media'] = [];
        $item['media']['poster'] = '';

        if (
            isset($this->item['service']['media']['posters'])
        ) {
            $item['media']['poster'] = "https://image.tmdb.org/t/p/original" . $this->item['service']['media']['posters']['file_path'];
        } else {

            if (
                isset($this->item['trakt']['ids']['tmdb']) &&
                !empty($this->item['trakt']['ids']['tmdb'])
            ) {

                if (
                    get_class($this->item) == Movie::class
                ) {
                    $imageAssets = ImageService::getImageMeta(ImageService::getMovieMedia($this->item['trakt']['ids']['tmdb']));

                    $service = $this->item['service'];
                    $serviceUpdate = $service;
                    $serviceUpdate['media'] = $imageAssets;

                    $this->item['service'] = $serviceUpdate;
                    $this->item->save();


                    $item['media']['poster'] = "https://image.tmdb.org/t/p/original" . $this->item['service']['media']['posters']['file_path'];
                }
            }
        }

        return view('domains.trakt.livewire.match.single', [
            'single' => $item
        ]);
    }
}
