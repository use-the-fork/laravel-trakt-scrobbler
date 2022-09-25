<?php

namespace App\Domains\Common\Models;

use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Model;
use App\Domains\Common\Models\Traits\HasMedia;
use App\Domains\Common\Models\Traits\HasTrakt;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Movie extends Model
{
    use HasFactory, HasMedia, HasTrakt;

    protected $fillable = [
        'service_id',
        'item_id',
        'title',
        'year',
        'watched_at',
        'progress',
        'released_at',
    ];

    public function getEncoded(): string
    {
        return urlencode(Str::lower("{$this->title}"));
    }

    public function getSlug(): string
    {
        return Str::slug("{$this->title}");
    }

    public function getTraktType(): string
    {
        return 'movies';
    }

    public function getTraktURL(): string
    {
        if (
            $this->traktable &&
            $this->traktable['ids']['slug']
        ) {
            return "https://trakt.tv/movies/" . $this->traktable['ids']['slug'];
        } else {
            return 'https://trakt.tv/search';
        }
    }

    public function service()
    {
        return $this->belongsTo(Service::class);
    }
}
