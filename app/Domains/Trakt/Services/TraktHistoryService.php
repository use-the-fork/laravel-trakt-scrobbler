<?php

namespace App\Domains\Trakt\Services;

use Carbon\Carbon;
use Illuminate\Support\Str;
use App\Domains\Trakt\Models\Trakt;
use App\Domains\Common\Models\Movie;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use App\Domains\Common\Models\Episode;


class TraktHistoryService extends TraktApiService
{

    public function unsync($request)
    {

        //See if we need to refresh the token before getting history

        $this->refreshToken();
        $response = Http::retry(3, 1000)->withHeaders([
            'Content-Type' => 'application/json',
            'trakt-api-version' => $this->apiVersion,
            'trakt-api-key' => $this->client_id
        ])->acceptJson()->withToken($this->traktConfig['config']['access_token'])->post($this->syncUrl . "/remove", $request)->throw()->json();


        return $response;
    }

    public function sync($request)
    {

        //See if we need to refresh the token before getting history

        $this->refreshToken();
        $response = Http::retry(3, 1000)->withHeaders([
            'Content-Type' => 'application/json',
            'trakt-api-version' => $this->apiVersion,
            'trakt-api-key' => $this->client_id
        ])->acceptJson()->withToken($this->traktConfig['config']['access_token'])->post($this->syncUrl, $request)->throw()->json();


        return $response;
    }

    public function getHistory($type = null, $id = null, $startDate = null, $endDate = null, $page = 0, $limit = 10000)
    {


        $theUrl = Str::of("{$this->syncUrl}/");
        if ($type !== null) {
            $theUrl = $theUrl->append("{$type}/{$id}/");
        }
        $theUrl = $theUrl->append("?page={$page}")->append("&limit={$limit}");

        if ($startDate !== null) {
            $startDate = Carbon::parse($startDate)->format('c');
            $theUrl = $theUrl->append("&start_at={$startDate}");
        }

        if ($endDate !== null) {
            $endDate = Carbon::parse($endDate)->format('c');
            $theUrl = $theUrl->append("&end_at={$endDate}");
        }

        //See if we need to refresh the token before getting history

        $this->refreshToken();
        $response = Http::retry(3, 1000)->withHeaders([
            'Content-Type' => 'application/json',
            'trakt-api-version' => $this->apiVersion,
            'trakt-api-key' => $this->client_id
        ])->acceptJson()->withToken($this->traktConfig['config']['access_token'])->get($theUrl->toString())->throw()->json();


        return $response;
    }
}
