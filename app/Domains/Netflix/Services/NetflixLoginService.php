<?php

	namespace App\Domains\Netflix\Services;

	use App\Domains\Netflix\Enums\NetflixConstant;

	class NetflixLoginService extends NetflixService
	{

		public $additionalChromeOptions = [
			'headless' => FALSE
		];

		public function login()
		{
			$page = $this->browser->createPage();
			$navigation = $page->navigate(NetflixConstant::LOGIN_URL);
			$navigation->waitForNavigation();
			sleep(15);
		}
	}
