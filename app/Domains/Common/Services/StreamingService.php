<?php

	namespace App\Domains\Common\Services;

	use HeadlessChromium\BrowserFactory;

	abstract class StreamingService
	{

		public $browser;
		public $isActivated = FALSE;
		public $additionalChromeOptions = [];

		public function __construct()
		{

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
		public function convertUnixDate($date){}
	}
