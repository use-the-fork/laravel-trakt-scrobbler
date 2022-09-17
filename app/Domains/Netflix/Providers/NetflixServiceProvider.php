<?php

namespace App\Domains\Netflix\Providers;

use Illuminate\Console\Scheduling\Schedule;

use App\Domains\Netflix\Commands\NetflixCommands;
use Illuminate\Support\ServiceProvider;

final class NetflixServiceProvider extends ServiceProvider
{

    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                NetflixCommands::class
            ]);
        }

        $this->app->afterResolving(Schedule::class, function (Schedule $schedule) {
            //$schedule->command(Commands\MyAwesomeCommand::class)->everyMinute();
        });
    }
}
