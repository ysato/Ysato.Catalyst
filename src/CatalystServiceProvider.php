<?php

declare(strict_types=1);

namespace Ysato\Catalyst;

use Illuminate\Support\ServiceProvider;
use Ysato\Catalyst\Console\NewProjectScaffoldingCommand;

class CatalystServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                NewProjectScaffoldingCommand::class,
            ]);
        }
    }
}
