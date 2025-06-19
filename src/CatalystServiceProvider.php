<?php

declare(strict_types=1);

namespace Ysato\Catalyst;

use Illuminate\Support\ServiceProvider;
use Override;
use Ysato\Catalyst\Console\ScaffoldCommand;

class CatalystServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    #[Override]
    public function register(): void
    {
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // phpcs:ignore SlevomatCodingStandard.ControlStructures.EarlyExit.EarlyExitNotUsed
        if ($this->app->runningInConsole()) {
            $this->commands([
                ScaffoldCommand::class,
            ]);
        }
    }
}
