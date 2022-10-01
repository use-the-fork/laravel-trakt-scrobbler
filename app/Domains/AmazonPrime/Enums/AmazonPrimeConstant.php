<?php

namespace App\Domains\AmazonPrime\Enums;

use App\Domains\Common\Enums\Enum as BaseEnum;

class AmazonPrimeConstant extends BaseEnum
{
    const HOST_URL         = "https://www.amazon.com";
    const LOGIN_URL         = "https://www.amazon.com/ap/signin?openid.pape.max_auth_age=0&openid.return_to=https%3A%2F%2Fwww.amazon.com%2FAmazon-Video%2Fb%2F%3F%26node%3D2858778011%26ref%3Dnav_signin&openid.identity=http%3A%2F%2Fspecs.openid.net%2Fauth%2F2.0%2Fidentifier_select&openid.assoc_handle=usflex&openid.mode=checkid_setup&openid.claimed_id=http%3A%2F%2Fspecs.openid.net%2Fauth%2F2.0%2Fidentifier_select&openid.ns=http%3A%2F%2Fspecs.openid.net%2Fauth%2F2.0&";
    const WATCHED_HISTORY_URL         = "https://www.amazon.com/gp/video/settings/watch-history/ref=dv_auth_ret&";
}
