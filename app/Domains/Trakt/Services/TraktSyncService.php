<?php

	namespace App\Domains\Trakt\Services;

	use Illuminate\Support\Facades\Cache;

	class TraktSyncService extends TraktApiService
	{

		public function getHistory() {
			//See if we need to refresh the token before getting history
			$this->refreshToken();

			$trakt = json_decode(Cache::get('trakt'), true);

			$client = new \GuzzleHttp\Client();
			$res = $client->request('GET', $this->syncUrl, [
				'headers' => [
					'Content-Type' => 'application/json',
					'Authorization' => "Bearer {$trakt['access_token']['token']}",
					'trakt-api-version' => $this->apiVersion,
					'trakt-api-key' => $this->client_id
				],
			]);

			$theContents = json_decode($res->getBody()->getContents(), TRUE);
			dd($theContents);

		}

	}
