<?php

namespace App\Domains\Trakt\Services;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\Http;

class TraktSearchService extends TraktApiService
{

    public function getMovieSummary($id)
    {
        //See if we need to refresh the token before getting history
        $this->refreshToken();

        return Http::retry(3, 1000)->withHeaders([
            'Content-Type' => 'application/json',
            'trakt-api-version' => $this->apiVersion,
            'trakt-api-key' => $this->client_id
        ])->acceptJson()
            ->withToken($this->traktConfig['config']['access_token'])
            ->get("{$this->apiUrl}/movies/{$id}?extended=full")
            ->throw()->json();
    }

    public function getShowSummary($id)
    {
        //See if we need to refresh the token before getting history
        $this->refreshToken();

        return Http::retry(3, 1000)->withHeaders([
            'Content-Type' => 'application/json',
            'trakt-api-version' => $this->apiVersion,
            'trakt-api-key' => $this->client_id
        ])->acceptJson()
            ->withToken($this->traktConfig['config']['access_token'])
            ->get("{$this->apiUrl}/shows/{$id}?extended=full")
            ->throw()->json();
    }

    public function getEpisodeSummary($id, $seasons, $episode)
    {
        //See if we need to refresh the token before getting history
        $this->refreshToken();

        return Http::retry(3, 1000)->withHeaders([
            'Content-Type' => 'application/json',
            'trakt-api-version' => $this->apiVersion,
            'trakt-api-key' => $this->client_id
        ])->acceptJson()
            ->withToken($this->traktConfig['config']['access_token'])
            ->get("{$this->apiUrl}/shows/{$id}/seasons/{$seasons}/episodes/{$episode}?extended=full")
            ->throw()->json();
    }

    public function search($type, $item)
    {

        //See if we need to refresh the token before getting history
        $this->refreshToken();

        return Http::retry(3, 1000)->withHeaders([
            'Content-Type' => 'application/json',
            'trakt-api-version' => $this->apiVersion,
            'trakt-api-key' => $this->client_id
        ])->acceptJson()
            ->withToken($this->traktConfig['config']['access_token'])
            ->get("{$this->searchUrl}/{$type}?query=" . $item . "&extended=full")
            ->throw()->json();
    }

    public function searchEpisode($id, $season, $episode)
    {

        //See if we need to refresh the token before getting history
        $this->refreshToken();

        if (app()->runningInConsole()) {
            echo "\n{$this->showUrl}/{$id}/seasons/{$season}/episodes/{$episode}";
        }

        return Http::retry(3, 1000)->withHeaders([
            'Content-Type' => 'application/json',
            'trakt-api-version' => $this->apiVersion,
            'trakt-api-key' => $this->client_id
        ])->acceptJson()
            ->withToken($this->traktConfig['config']['access_token'])
            ->get("{$this->showUrl}/{$id}/seasons/{$season}/episodes/{$episode}")
            ->throw()->json();
    }

    public function compareServiceMatch($type, $item, $match)
    {

        if ($type == 'show') {
            $serviceId = $item->show->service_id;
        } else {
            $serviceId = $item->service_id;
        }

        if (
            isset($match[$type]['homepage']) &&
            Str::contains($match[$type]['homepage'], $serviceId)
        ) {
            return true;
        }

        return false;
    }

    public function compareMatch($type, $item, $match)
    {

        $itemName = Str::of($item->title)->lower()->slug()->toString();
        $traktName = Str::of($match[$type]['title'])->lower()->slug()->toString();


        if ($type == 'show') {
            $serviceId = $item->show->service_id;
        } else {
            $serviceId = $item->service_id;
        }

        if (
            !empty($item->year) &&
            $itemName == $traktName &&
            $item->year == $match[$type]['year']
        ) {
            return true;
        }

        if (
            !empty($item->year) &&
            $itemName == $traktName &&
            $item->year == $match[$type]['year']
        ) {
            return true;
        }

        if (
            $itemName == $traktName
        ) {
            return true;
        }

        return false;
    }
}
