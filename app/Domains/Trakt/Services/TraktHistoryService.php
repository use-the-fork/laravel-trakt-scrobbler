<?php

namespace App\Domains\Trakt\Services;

use Illuminate\Support\Facades\Http;
use App\Domains\Common\Models\Episode;
use App\Domains\Common\Models\Movie;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;


class TraktHistoryService extends TraktApiService
{

	public function sync($request)
	{

		//See if we need to refresh the token before getting history

		$this->refreshToken();
		$response = Http::withHeaders([
			'Content-Type' => 'application/json',
			'trakt-api-version' => $this->apiVersion,
			'trakt-api-key' => $this->client_id
		])->acceptJson()->withToken($this->traktConfig['config']['access_token'])->post($this->syncUrl, $request)->throw()->json();


		return $response;
	}

	public function getHistory($type, $id)
	{

		//See if we need to refresh the token before getting history

		$this->refreshToken();
		$response = Http::withHeaders([
			'Content-Type' => 'application/json',
			'trakt-api-version' => $this->apiVersion,
			'trakt-api-key' => $this->client_id
		])->acceptJson()->withToken($this->traktConfig['config']['access_token'])->get("{$this->syncUrl}/{$type}/{$id}")->throw()->json();


		return $response;
	}

	public function searchEpisode($id, $season, $episode)
	{

		//See if we need to refresh the token before getting history
		$this->refreshToken();

		$trakt = json_decode(Cache::get('trakt'), true);

		$client = new \GuzzleHttp\Client();
		$res = $client->request('GET', "{$this->showUrl}/{$id}/seasons/{$season}/episodes/{$episode}", [
			'headers' => [
				'Content-Type' => 'application/json',
				'Authorization' => "Bearer {$trakt['access_token']['token']}",
				'trakt-api-version' => $this->apiVersion,
				'trakt-api-key' => $this->client_id
			],
		]);

		return json_decode($res->getBody()->getContents(), TRUE);
	}
}
