<?php

	namespace App\Domains\Trakt\Services;

	use Illuminate\Support\Facades\Cache;
	use Illuminate\Support\Str;

	class TraktAuthService extends TraktApiService
	{


		public $additionalChromeOptions = [
			'headless' => false
		];

		public function authorize()
		{
			$trakt = json_decode(Cache::get('trakt'), true);

			$page = $this->browser->createPage();
			$navigation = $page->navigate('https://trakt.tv/auth/signin');
			$navigation->waitForNavigation();


			if(
				Str::contains($page->getCurrentUrl(), '/signin')
			) {
				$page->mouse()->find('#user_login')->click();
				$page->keyboard()->typeText($trakt['username'])->setKeyInterval(10);

				sleep(2);
				$page->mouse()->find('#user_password')->click();
				$page->keyboard()->typeText($trakt['password'])->setKeyInterval(10);
				sleep(1);
				$page->mouse()->find('.form-actions .btn-primary')->click();

				sleep(5);
			}

			if(
				Str::contains($page->getCurrentUrl(), '/dashboard')
			){
				//Ready to Auth
				$navigation = $page->navigate($this->getAuthorizeUrl());
				$navigation->waitForNavigation();
				sleep(5);

				//$page->mouse()->find('.btn-success')->click();
				//sleep(5);

				$pageSearch = $page->evaluate('window.location.search')->getReturnValue();
				if(
					Str::contains($pageSearch, 'code=')
				){
					$code = Str::of($page->getCurrentUrl())->afterLast('=')->toString();
					$trakt['code'] = $code;
					Cache::put('trakt', json_encode($trakt));

					//Exchnage the key for a token
					$this->requestToken();


					return true;
				}
			}
			return false;
		}

		public function requestToken() {

			$trakt = json_decode(Cache::get('trakt'), true);

			$request = [
				"code" => $trakt['code'],
				  "client_id" => "5b2c413fdba09224fd8b7188b98b1f8b2f72ecd672d2a21639b11b55bc1776de",
				  "client_secret" => "56bf50a5febac897cb54a713fadf6029b148d208eb9a4a3c972074c10c28483d",
				  "redirect_uri" => $this->redirectUrl,
				  "grant_type" => "authorization_code"
			];

			$client = new \GuzzleHttp\Client();
			$res = $client->request('POST', 'https://api.trakt.tv/oauth/token', [
				'headers' => [
					'Content-Type' => 'application/json'
				],
				'body' => json_encode($request)
			]);

			$theContents = json_decode($res->getBody()->getContents(), TRUE);

			$trakt['access_token'] = [
				'token' => $theContents['access_token'],
				'expires_at' => $theContents['created_at'] + $theContents['expires_in'],
			];

			$trakt['refresh_token'] = $theContents['refresh_token'];

			Cache::put('trakt', json_encode($trakt));
		}

		public function exchangeToken() {

			$trakt = json_decode(Cache::get('trakt'), true);

			$request = [
				"refresh_token" => $trakt['refresh_token'],
				  "client_id" => $this->client_id,
				  "client_secret" => $this->client_secret,
				  "redirect_uri" => $this->redirectUrl,
				  "grant_type" => "refresh_token"
			];

			$client = new \GuzzleHttp\Client();
			$res = $client->request('POST', 'https://api.trakt.tv/oauth/token', [
				'headers' => [
					'Content-Type' => 'application/json'
				],
				'body' => json_encode($request)
			]);

			$theContents = json_decode($res->getBody()->getContents(), TRUE);

			$trakt['access_token'] = [
				'token' => $theContents['access_token'],
				'expires_at' => $theContents['created_at'] + $theContents['expires_in'],
			];

			$trakt['refresh_token'] = $theContents['refresh_token'];

			Cache::put('trakt', json_encode($trakt));
		}


		public function getAuthorizeUrl() {
			return "{$this->authorizeUrl}?response_type=code&client_id=5b2c413fdba09224fd8b7188b98b1f8b2f72ecd672d2a21639b11b55bc1776de&redirect_uri={$this->getRedirectUrl()}";
		}

		public function getRedirectUrl() {
			return $this->redirectUrl;
		}
	}
