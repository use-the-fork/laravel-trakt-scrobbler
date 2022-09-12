<?php

	namespace App\Domains\Trakt\Providers;

	use App\Domains\Trakt\Commands\Setup;
	use Illuminate\Support\ServiceProvider;

	final class TraktServiceProvider extends ServiceProvider
	{

		public function boot(): void
		{
			if ($this->app->runningInConsole()) {
				$this->commands([
									Setup::class,
								]);

			}
		}
	}
