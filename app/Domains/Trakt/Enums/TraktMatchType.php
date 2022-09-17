<?php

namespace App\Domains\Trakt\Enums;

use App\Domains\Common\Enums\Enum as BaseEnum;

class TraktMatchType extends BaseEnum
{
    const SERVICE         = "service";
    const SINGLE        = "single";
    const COMPARED         = "compare";
    const NONE         = "none";
}
