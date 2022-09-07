<?php

	namespace App\Domains\Common\Models;

	use Illuminate\Database\Eloquent\Factories\HasFactory;
	use Illuminate\Database\Eloquent\Model;
	use Illuminate\Support\Str;

	class Show extends Model
	{
		use HasFactory;

		protected $casts = [
			'trakt'   => 'array',
			'service' => 'array',
		];
		protected $fillable = [
			'service_id',
			'item_id',
			'title',
			'year',
			'watched_at',
			'progress',
			'trakt',
			'service',
			'sync',
			'release_date',
		];

		/**
		 * Get the comments for the blog post.
		 */
		public function episodes()
		{
			return $this->hasMany(Episode::class);
		}

		public function getSlug(): string
		{
			return Str::slug("{$this->title}");
		}

		public function service()
		{
			return $this->belongsTo(Service::class);
		}

	}
