<?php

	namespace App\Domains\Trakt\Commands;

	use App\Domains\Common\Models\Service;
	use App\Domains\Netflix\Services\NetflixLoginService;
	use App\Domains\Trakt\Services\TraktAuthService;
	use Illuminate\Console\Command;

	class Setup extends Command
	{
		/**
		 * The console command description.
		 *
		 * @var string
		 */
		protected $description = 'Setup Trakt operations for sync';
		/**
		 * The name and signature of the console command.
		 *
		 * @var string
		 */
		protected $signature = 'service:trakt:setup';

		/**
		 * Execute the console command.
		 *
		 * @return int
		 */
		public function handle()
		{

			//Check to see if we have a config setup
			$trakt = Service::firstOrNew([
											   'name' => 'trakt'
										   ]);
			$trakt->config = [
				'access_token' => NULL,
				'expires_at' => NULL,
				'refresh_token' => NULL,
			];

			$trakt->save();

			if ($this->confirm('A new browserless chrome window will now open and ask you to login to trakt and authenticate the app.', TRUE)) {
				$traktAuthService = new TraktAuthService();
				$traktAuthService->authorize();

			}

			return 0;
		}
	}
