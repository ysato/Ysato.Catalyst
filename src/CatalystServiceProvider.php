<?php

declare(strict_types=1);

namespace Ysato\Catalyst;

use Illuminate\Support\ServiceProvider;
use Spatie\TemporaryDirectory\TemporaryDirectory;
use Ysato\Catalyst\Console\ScaffoldCommand;

class CatalystServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app
            ->when(ScaffoldCommand::class)
            ->needs(TemporaryDirectory::class)
            ->give(static function () {
                return (new TemporaryDirectory())
                    ->deleteWhenDestroyed()
                    ->create();
            });
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
