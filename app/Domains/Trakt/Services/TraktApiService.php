<?php

	namespace App\Domains\Trakt\Services;

	use Carbon\Carbon;
	use HeadlessChromium\BrowserFactory;
	use Illuminate\Support\Facades\Cache;

	abstract class TraktApiService
	{

		public $browser;
		public $isActivated = FALSE;

		public $apiVersion = '2';
		public $hostUrl = 'https://trakt.tv';
		public $apiUrl = 'https://api.trakt.tv';
		public $authorizeUrl;
		public $redirectUrl;
		public $requestTokenUrl;
		public $revokeTokenUrl;
		public $searchUrl;
		public $showUrl;
		public $scrobbleUrl;
		public $syncUrl;
		public $settingsUrl;

		public $client_id = '5b2c413fdba09224fd8b7188b98b1f8b2f72ecd672d2a21639b11b55bc1776de';
		public $client_secret = '56bf50a5febac897cb54a713fadf6029b148d208eb9a4a3c972074c10c28483d';

		public $additionalChromeOptions = [];

		public function __construct()
		{

			$this->authorizeUrl = "{$this->hostUrl}/oauth/authorize";
			$this->redirectUrl = "{$this->hostUrl}/apps";
			$this->requestTokenUrl = "{$this->apiUrl}/oauth/token";
			$this->revokeTokenUrl = "{$this->apiUrl}/oauth/revoke";
			$this->searchUrl = "{$this->apiUrl}/search";
			$this->showUrl = "{$this->apiUrl}/shows";
			$this->scrobbleUrl = "{$this->apiUrl}/scrobble";
			$this->syncUrl = "{$this->apiUrl}/sync/history";
			$this->settingsUrl = "{$this->apiUrl}/users/settings";



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

		public function activate(){}

		public function refreshToken(){
			$trakt = json_decode(Cache::get('trakt'), true);

			if(
				(Carbon::parse($trakt['access_token']['expires_at']))->isPast()
			){
				(new TraktAuthService())->exchangeToken();
			}
		}
	}
