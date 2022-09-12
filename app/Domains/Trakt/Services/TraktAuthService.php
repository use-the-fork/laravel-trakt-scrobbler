<?php

	namespace App\Domains\Trakt\Services;

	use HeadlessChromium\BrowserFactory;
	use Illuminate\Support\Str;

	class TraktAuthService extends TraktApiService
	{

		public $additionalChromeOptions = [
			'headless' => FALSE
		];

		public function __construct()
		{

			parent::__construct();

			$browserFactory = new BrowserFactory('google-chrome');
			$this->browser = $browserFactory->createBrowser([
																'userDataDir' => storage_path() . '/browser_storage',
																...$this->additionalChromeOptions
															]);
		}


		public function __destruct()
		{
			$this->browser->close();
		}


		public function authorize()
		{

			$page = $this->browser->createPage();
			$navigation = $page->navigate('https://trakt.tv/auth/signin');
			sleep(10);

			if (
				Str::contains($page->getCurrentUrl(), '/signin')
			) {
				sleep(15);
			}

			//Ready to Auth
			$navigation = $page->navigate($this->getAuthorizeUrl());
			sleep(15);

			$pageSearch = $page->evaluate('window.location.search')->getReturnValue();
			if (
				Str::contains($pageSearch, 'code=')
			) {
				$code = Str::of($page->getCurrentUrl())->afterLast('=')->toString();

				//Exchnage the key for a token
				return $this->requestToken($code);
			}

			return FALSE;
		}

		public function getAuthorizeUrl()
		{
			return "{$this->authorizeUrl}?response_type=code&client_id=5b2c413fdba09224fd8b7188b98b1f8b2f72ecd672d2a21639b11b55bc1776de&redirect_uri={$this->getRedirectUrl()}";
		}

		public function getRedirectUrl()
		{
			return $this->redirectUrl;
		}

		public function requestToken($code)
		{

			$request = [
				"code"          => $code,
				"client_id"     => "5b2c413fdba09224fd8b7188b98b1f8b2f72ecd672d2a21639b11b55bc1776de",
				"client_secret" => "56bf50a5febac897cb54a713fadf6029b148d208eb9a4a3c972074c10c28483d",
				"redirect_uri"  => $this->redirectUrl,
				"grant_type"    => "authorization_code"
			];

			$client = new \GuzzleHttp\Client();
			$res = $client->request('POST', 'https://api.trakt.tv/oauth/token', [
				'headers' => [
					'Content-Type' => 'application/json'
				],
				'body'    => json_encode($request)
			]);

			$theContents = json_decode($res->getBody()->getContents(), TRUE);

			$trakt['access_token'] = $theContents['access_token'];
			$trakt['expires_at'] = $theContents['created_at'] + $theContents['expires_in'];
			$trakt['refresh_token'] = $theContents['refresh_token'];

			$this->traktConfig['config'] = $trakt;
			$this->traktConfig->save();

			return TRUE;
		}

		public function exchangeToken()
		{

			$request = [
				"refresh_token" => $this->traktConfig['config']['refresh_token'],
				"client_id"     => $this->client_id,
				"client_secret" => $this->client_secret,
				"redirect_uri"  => $this->redirectUrl,
				"grant_type"    => "refresh_token"
			];

			$client = new \GuzzleHttp\Client();
			$res = $client->request('POST', 'https://api.trakt.tv/oauth/token', [
				'headers' => [
					'Content-Type' => 'application/json'
				],
				'body'    => json_encode($request)
			]);

			$theContents = json_decode($res->getBody()->getContents(), TRUE);

			$trakt['access_token'] = $theContents['access_token'];
			$trakt['expires_at'] = $theContents['created_at'] + $theContents['expires_in'];
			$trakt['refresh_token'] = $theContents['refresh_token'];

			$this->traktConfig['config'] = $trakt;
			$this->traktConfig->save();

		}
	}
