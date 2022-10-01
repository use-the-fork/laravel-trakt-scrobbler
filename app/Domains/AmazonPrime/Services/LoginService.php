<?php

namespace App\Domains\AmazonPrime\Services;

use App\Domains\AmazonPrime\Enums\AmazonPrimeConstant;

class LoginService extends AmazonPrimeService
{

    public $additionalChromeOptions = [
        'headless' => FALSE
    ];

    public function login()
    {
        $page = $this->browser->createPage();
        $navigation = $page->navigate(AmazonPrimeConstant::LOGIN_URL);
        $navigation->waitForNavigation();
        sleep(45);
    }
}
