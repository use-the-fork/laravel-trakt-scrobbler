<?php

namespace App\Domains\AmazonPrime\Commands;

use Illuminate\Console\Command;
use App\Domains\Common\Models\Service;
use App\Domains\AmazonPrime\Services\AmazonPrimeService;
use App\Domains\AmazonPrime\Services\LoginService;

class Commands extends Command
{
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Amazon Prime operations for sync';
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'service:amazon-prime';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $amazon = Service::firstOrNew([
            'name' => 'amazon prime'
        ]);


        if (!$amazon->exists) {
            $this->setup();
        }

        $command = $this->choice(
            'Please select what you would like to do',
            ['Sync History', 'Sync Meta', 'Dispatch Job', 'Run Setup']
        );

        switch ($command) {
            case 'Sync History':
                $this->syncHistory();
                return;
            case 'Dispatch Job':
                $this->dispatchJob();
                return;
            case 'Run Setup':
                $this->setup();
                return;
        }
    }

    private function dispatchJob()
    {

        $command = $this->choice(
            'Please select what you would like to do',
            ['Sync Meta']
        );

        switch ($command) {
            case 'Sync Meta':
                dispatch(new ProcessNetflixMeta());
                return;
            case 'Dispatch Job':
                $this->dispatchJob();
                return;
        }

        return true;
    }

    private function syncHistory()
    {
        $netflixService = new AmazonPrimeService();
        $netflixService->loadHistoryItems(1000);
        return true;
    }

    private function setup()
    {
        //Check to see if we have a config setup
        $amazon = Service::where('name', 'amazon prime')->firstOrNew();
        $amazon->name = 'amazon prime';
        $amazon->config = [
            'isActive' => FALSE,
            'lastHistorySync' => null,
            'auth' => [
                'expires_at' => null,
            ],
        ];
        $amazon->save();


        if ($amazon->config['isActive'] == FALSE) {


            if ($this->confirm('A new browserless chrome window will now open and ask you to login to netflix.', TRUE)) {
                $service = new LoginService();
                $service->login();

                //see if service is active
                $service->isActive();
            }
        }
    }
}
