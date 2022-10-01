<?php

namespace App\Domains\Common\Services;

use HeadlessChromium\BrowserFactory;

abstract class StreamingService
{

    public $browser;
    public $browserFactory;
    public $isActivated = FALSE;
    public $additionalChromeOptions = [];

    public function __construct()
    {

        $this->browserFactory = new BrowserFactory('google-chrome');
        $this->browserFactory->setOptions([
            'userDataDir' => storage_path() . '/browser_storage',
            ...$this->additionalChromeOptions
        ]);

        $this->browser = $this->browserFactory->createBrowser();
    }

    public function __destruct()
    {
        $this->browser->close();
    }

    public function activate()
    {
    }
    public function convertUnixDate($date)
    {
    }
}
