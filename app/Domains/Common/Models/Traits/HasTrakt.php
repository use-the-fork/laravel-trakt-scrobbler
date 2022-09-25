<?php

namespace App\Domains\Common\Models\Traits;

use App\Domains\Trakt\Models\Trakt;

trait HasTrakt
{


    public function traktable()
    {
        return $this->morphOne(Trakt::class, 'traktable');
    }

    public function saveTrakt(Trakt $trakt)
    {

        if (!$this->traktable) {
            return $this->traktable()->create($trakt->toArray());
        }

        return $this->traktable()->update($trakt->toArray());
    }
}
