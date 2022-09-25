<?php

namespace App\Domains\Trakt\Services;

use Illuminate\Support\Facades\Http;
use App\Domains\Common\Models\Episode;
use App\Domains\Common\Models\Movie;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;


class TraktHistoryService extends TraktApiService
{

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
