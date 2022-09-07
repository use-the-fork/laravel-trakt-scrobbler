<?php

namespace App\Domains\Common\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Service extends Model
{
    use HasFactory;


	protected $fillable = [
		'name',
		'config'
	];

	protected $casts = [
		'config' => 'array',
	];

	public function movies()
	{
		return $this->hasMany(Movie::class);
	}

	public function shows()
	{
		return $this->hasMany(Show::class);
	}

	public function episodes()
	{
		return $this->hasMany(Episode::class);
	}

}
