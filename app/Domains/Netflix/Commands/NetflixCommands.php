<?php

namespace App\Domains\Netflix\Commands;

use Illuminate\Console\Command;
use App\Domains\Common\Models\Service;
use App\Domains\Netflix\Jobs\ProcessNetflixMeta;
use App\Domains\Netflix\Services\NetflixService;
use App\Domains\Netflix\Services\NetflixLoginService;

class NetflixCommands extends Command
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
    protected $signature = 'service:netflix';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $netflix = Service::firstOrNew([
            'name' => 'netflix'
        ]);


        if (!$netflix->exists) {
            $this->setup();
        }

        $command = $this->choice(
            'Please select what you would like to do',
            ['Sync History', 'Sync Meta', 'Dispatch Job']
        );

        switch ($command) {
            case 'Sync History':
                $this->syncHistory();
                return;
            case 'Dispatch Job':
                $this->dispatchJob();
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
        $netflixService = new NetflixService();
        $netflixService->loadHistoryItems(1000);
        return true;
    }

    private function setup()
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
    }
}
