<?php

	namespace App\Domains\Trakt\Services;

	use App\Domains\Common\Models\Episode;
	use App\Domains\Common\Models\Movie;
	use Illuminate\Support\Facades\Cache;
	use Illuminate\Support\Str;

	class TraktHistoryService extends TraktApiService
	{

		public function createSyncObject() {

			//Get all movies and add them to they sync object
			//$movies =

		}

		public function search($type, $item) {

			//See if we need to refresh the token before getting history
			$this->refreshToken();

			$trakt = json_decode(Cache::get('trakt'), true);

			$client = new \GuzzleHttp\Client();
			$res = $client->request('GET', "{$this->searchUrl}/{$type}?query=" . $item->getSlug() . "&extended=full", [
				'headers' => [
					'Content-Type' => 'application/json',
					'Authorization' => "Bearer {$trakt['access_token']['token']}",
					'trakt-api-version' => $this->apiVersion,
					'trakt-api-key' => $this->client_id
				],
			]);

			return json_decode($res->getBody()->getContents(), TRUE);
		}

		public function searchEpisode($id, $season, $episode ) {

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
