<?php

namespace App\Domains\Common\Models\Traits;

use App\Domains\Common\Models\Episode;
use App\Domains\Common\Models\Media;
use App\Domains\Common\Models\Movie;
use App\Domains\TMDB\Services\ImageService;

trait HasMedia
{


    public function media()
    {
        return $this->morphOne(Media::class, 'mediaable');
    }

    public function saveMedia(Media $media)
    {
        if (!$this->media) {
            return $this->media()->create($media->toArray());
        }

        return $this->media()->update($media->toArray());
    }

    public function getPoster()
    {

        $thePoster = asset('img/logos/trakt/trakt-icon-red-white.svg');;
        if (
            isset($this->media->poster)
        ) {

            $thePoster = "https://image.tmdb.org/t/p/original" . $this->media->poster['file_path'];
        } else {
            $this->getMedia();
            if (isset($this->media->poster)) {
                $thePoster = "https://image.tmdb.org/t/p/original" . $this->media->poster['file_path'];
            }
        }

        return $thePoster;
    }

    public function getBackdrop()
    {

        $theBackdrop = asset('img/logos/trakt/trakt-icon-red-white.svg');;
        if (
            isset($this->media->backdrop)
        ) {
            $theBackdrop = "https://image.tmdb.org/t/p/original" . $this->media->backdrop['file_path'];
        } else {
            $this->getMedia();
            if (isset($this->media->backdrop)) {
                $theBackdrop = "https://image.tmdb.org/t/p/original" . $this->media->backdrop['file_path'];
            }
        }

        return $theBackdrop;
    }

    private function getMedia()
    {
        if (
            get_class($this) == Movie::class &&
            isset($this->traktable['ids']['tmdb'])
        ) {

            $imageAssets = ImageService::getImageMeta(ImageService::getMovieMedia($this->traktable['ids']['tmdb']));
            $theMedia = new Media();

            if (isset($imageAssets['posters'])) {
                $theMedia->poster = $imageAssets['posters'];
            }

            if (isset($imageAssets['logos'])) {
                $theMedia->logo = $imageAssets['logos'];
            }

            if (isset($imageAssets['backdrops'])) {
                $theMedia->backdrop = $imageAssets['backdrops'];
            }

            $this->saveMedia($theMedia);
            $this->refresh();
        } else if (
            get_class($this) === Episode::class &&
            isset($this->traktable['ids']['tmdb'])
        ) {

            $theMedia = new Media();
            $theEpisodeMedia = new Media();

            $imageAssets = ImageService::getImageMeta(ImageService::getShowMedia($this->show->traktable['ids']['tmdb']));

            if (isset($imageAssets['posters'])) {
                $theMedia->poster = $imageAssets['posters'];
                $theEpisodeMedia->poster = $imageAssets['posters'];
            }

            if (isset($imageAssets['logos'])) {
                $theMedia->logo = $imageAssets['logos'];
                $theEpisodeMedia->logo = $imageAssets['logos'];
            }

            if (isset($imageAssets['backdrops'])) {
                $theMedia->backdrop = $imageAssets['backdrops'];
            }

            $this->show->saveMedia($theMedia);

            $imageAssets = ImageService::getImageMeta(ImageService::getEpisodeMedia($this->show->traktable['ids']['tmdb'], $this->season, $this->number));

            if (isset($imageAssets['stills'])) {
                $theEpisodeMedia->backdrop = $imageAssets['stills'];
            } else if (isset($imageAssets['backdrops'])) {
                $theEpisodeMedia->backdrop = $imageAssets['backdrops'];
            }

            $this->saveMedia($theEpisodeMedia);
            $this->refresh();
        }

        return $this->media;
    }
}
