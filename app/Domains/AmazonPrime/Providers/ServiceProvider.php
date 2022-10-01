<?php

namespace App\Domains\AmazonPrime\Providers;

use App\Domains\Common\Models\Service;

use Illuminate\Console\Scheduling\Schedule;
use App\Domains\AmazonPrime\Commands\Commands;
use Illuminate\Support\ServiceProvider as BaseServiceProvider;

final class ServiceProvider extends BaseServiceProvider
{

    public function boot(): void
    {

        if ($this->app->runningInConsole()) {
            $this->commands([
                Commands::class
            ]);
        }

        $this->app->afterResolving(Schedule::class, function (Schedule $schedule) {
            //$schedule->command(Commands\MyAwesomeCommand::class)->everyMinute();
        });
    }
}
