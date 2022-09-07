<?php

	namespace App\Console\Commands;

	use Illuminate\Console\Command;
	use Illuminate\Support\Facades\Cache;

	class setup extends Command
	{
		/**
		 * The console command description.
		 *
		 * @var string
		 */
		protected $description = 'Command description';
		/**
		 * The name and signature of the console command.
		 *
		 * @var string
		 */
		protected $signature = 'uts:setup';

		/**
		 * Execute the console command.
		 *
		 * @return int
		 */
		public function handle()
		{
			//$name = $this->ask('What is your name?');

			$value = Cache::get('trakt');

			if (!Cache::has('trakt')) {
				$this->info('Missing Trakt Info ');
			}

			dd($value);



		}
	}
