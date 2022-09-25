<?php

namespace App\Domains\Common\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Media extends Model
{
    use HasFactory;

    protected $casts = [
        'poster' => 'array',
        'backdrop' => 'array',
        'logo' => 'array',
    ];

    protected $fillable = [
        'poster',
        'backdrop',
        'logo'
    ];

    public function mediaable()
    {
        return $this->morphTo();
    }
}
