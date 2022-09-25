<?php

namespace App\Domains\Common\Models;

use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Model;
use App\Domains\Common\Models\Traits\HasMedia;
use App\Domains\Common\Models\Traits\HasTrakt;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Show extends Model
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
        'release_date',
    ];

    /**
     * Get the comments for the blog post.
     */

    public function getEncoded(): string
    {
        return urlencode(Str::lower("{$this->title}"));
    }

    public function episodes()
    {
        return $this->hasMany(Episode::class);
    }

    public function getSlug(): string
    {
        return Str::slug("{$this->title}");
    }

    public function getTraktURL(): string
    {
        if (
            $this->traktable &&
            $this->traktable['ids']['slug']
        ) {
            return "https://trakt.tv/shows/" . $this->traktable['ids']['slug'];
        } else {
            return 'https://trakt.tv/search';
        }
    }

    public function service()
    {
        return $this->belongsTo(Service::class);
    }
}
