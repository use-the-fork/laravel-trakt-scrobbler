<?php

namespace App\Domains\Trakt\Commands;

use Illuminate\Console\Command;
use App\Domains\Trakt\Jobs\MatchJob;
use App\Domains\Common\Models\Service;
use App\Domains\Trakt\Jobs\SyncHistoryFromTrakt;
use App\Domains\Trakt\Services\TraktAuthService;
use App\Domains\Netflix\Services\NetflixLoginService;

class TraktCommands extends Command
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
    protected $signature = 'service:trakt';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {

        $trakt = Service::firstOrNew([
            'name' => 'trakt'
        ]);

        if (!$trakt->exists) {
            $this->setup();
        }

        $command = $this->choice(
            'Please select what you would like to do',
            [
                'Sync History',
                'Sync Meta', 'Dispatch Job'
            ]
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
            ['Sync History From Trakt', 'Match Watch History']
        );

        switch ($command) {
            case 'Sync History From Trakt':
                dispatch(new SyncHistoryFromTrakt());
                return;
            case 'Match Watch History':
                dispatch(new MatchJob());
                return;
        }

        return true;
    }

    private function setup()
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
    }
}
