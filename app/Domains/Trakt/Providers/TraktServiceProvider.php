<?php

	namespace App\Domains\Netflix\Providers;

	use App\Domains\Netflix\Commands\Setup;
	use Illuminate\Support\ServiceProvider;

	final class NetflixServiceProvider extends ServiceProvider
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
