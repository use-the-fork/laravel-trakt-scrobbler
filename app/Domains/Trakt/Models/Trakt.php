<?php

namespace App\Domains\Trakt\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Trakt extends Model
{
    use HasFactory;


    protected $casts = [
        'ids'   => 'array',
        'information' => 'array',
    ];

    protected $fillable = [
        'trakt_id',
        'match_type',
        'ids',
        'information',
        'score',
        'sync_id',
        'watched_at',
        'status',
    ];


    public function traktable()
    {
        return $this->morphTo();
    }
}
