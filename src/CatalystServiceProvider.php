<?php

declare(strict_types=1);

namespace Ysato\Catalyst;

use Illuminate\Support\ServiceProvider;
use Ysato\Catalyst\Console\CatalystSetupCommand;

class CatalystServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->commands([CatalystSetupCommand::class]);
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}