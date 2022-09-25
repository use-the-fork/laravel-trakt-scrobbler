<?php

namespace App\Domains\Trakt\Enums;

use App\Domains\Common\Enums\Enum as BaseEnum;

class TraktStatus extends BaseEnum
{
    const READY_TO_APPROVE         = 0;
    const READY_TO_SYNC         = 1;
    const SYNCED         = 2;
    const ERROR         = 3;
}
