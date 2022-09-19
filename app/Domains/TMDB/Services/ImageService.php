<?php

namespace App\Domains\TMDB\Services;

use Illuminate\Support\Facades\Http;

class ImageService
{

    public static function getMovieMedia($id)
    {
        $api_key = env('TMDB_API');

        $response = Http::acceptJson()->get("https://api.themoviedb.org/3/movie/{$id}/images?api_key={$api_key}")->throw()->json();

        return $response;
    }

    public static function getImageMeta($imageObject)
    {

        if (isset($imageObject['backdrops'])) {
            $imageObject['backdrops'] = collect($imageObject['backdrops'])->sortBy([['vote_count', 'desc'], ['vote_average', 'desc']])->first();
        }

        if (isset($imageObject['logos'])) {
            $imageObject['logos'] = collect($imageObject['logos'])->sortBy([['vote_count', 'desc'], ['vote_average', 'desc']])->first();
        }

        if (isset($imageObject['posters'])) {
            $imageObject['posters'] = collect($imageObject['posters'])->sortBy([['vote_count', 'desc'], ['vote_average', 'desc']])->first();
        }

        return $imageObject;
    }
}
