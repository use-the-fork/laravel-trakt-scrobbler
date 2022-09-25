<?php

namespace App\Domains\Common\Models;

use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Model;
use App\Domains\Common\Models\Traits\HasMedia;
use App\Domains\Common\Models\Traits\HasTrakt;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Episode extends Model
{
    use
        HasFactory,
        HasMedia,
        HasTrakt;

    protected $fillable = [
        'service_id',
        'item_id',
        'title',
        'year',
        'watched_at',
        'progress',
        'released_at',
        'season',
        'number',
    ];

    public function getTraktType(): string
    {
        return 'episodes';
    }

    public function getTraktURL(): string
    {


        if (
            $this->show->traktable &&
            $this->show->traktable['ids']['slug']
        ) {
            return "https://trakt.tv/shows/" . $this->show->traktable['ids']['slug'];
        } else {
            return 'https://trakt.tv/search';
        }
    }

    public function getEncoded(): string
    {
        return urlencode(Str::lower("{$this->title}"));
    }

    public function getSlug(): string
    {
        return Str::slug("{$this->title}");
    }

    public function service()
    {
        return $this->belongsTo(Service::class);
    }

    public function show()
    {
        return $this->belongsTo(Show::class);
    }
}
