<?php

	namespace App\Domains\Trakt\Services;

	use App\Domains\Common\Models\Episode;
	use App\Domains\Common\Models\Movie;
	use Illuminate\Support\Facades\Cache;
	use Illuminate\Support\Str;

	class TraktSearchService extends TraktApiService
	{

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

		public function compareMatch($type, $item, $match) {

			$itemName = Str::of($item->title)->lower()->slug()->toString();
			$traktName = Str::of($match[$type]['title'])->lower()->slug()->toString();

			if($type == 'show'){
				$serviceId = $item->show->service_id;
			} else {
				$serviceId = $item->service_id;
			}

			if(
				isset($match[$type]['homepage']) &&
				Str::contains($match[$type]['homepage'], $serviceId)
			){
				return true;
			}

			if(
				!empty($item->year) &&
				$itemName == $traktName &&
				$item->year == $match[$type]['year']
			){
				return true;
			}

			if(
				!empty($item->year) &&
				$itemName == $traktName &&
				$item->year == $match[$type]['year']
			){
				return true;
			}

			if(
				$itemName == $traktName
			){
				return true;
			}

			return false;
		}

	}
