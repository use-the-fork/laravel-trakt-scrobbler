<?php

	namespace App\Domains\Netflix\Commands;

	use App\Domains\Common\Models\Service;
	use App\Domains\Netflix\Services\NetflixLoginService;
	use Illuminate\Console\Command;

	class Setup extends Command
	{
		/**
		 * The console command description.
		 *
		 * @var string
		 */
		protected $description = 'Setup Netflix operations for sync';
		/**
		 * The name and signature of the console command.
		 *
		 * @var string
		 */
		protected $signature = 'service:netflix:setup';

		/**
		 * Execute the console command.
		 *
		 * @return int
		 */
		public function handle()
		{

			//Check to see if we have a config setup
			$netflix = Service::firstOrNew([
											   'name' => 'netflix'
										   ]);
			$netflix->config = [
				'lastSync' => NULL,
				'isActive' => FALSE,
			];

			$netflix->save();

			if ($netflix->config['isActive'] == FALSE) {


				if ($this->confirm('A new browserless chrome window will now open and ask you to login to netflix.', TRUE)) {
					$netflixService = new NetflixLoginService();
					$netflixService->login();

					//see if service is active
					$netflixService->isActive();
				}
			}

			return 0;
		}
	}
