<?php

declare(strict_types=1);

namespace Ysato\Catalyst;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\ServiceProvider;
use Spatie\TemporaryDirectory\TemporaryDirectory;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use Ysato\Catalyst\Console\ArchitectureSrcSetupCommand;
use Ysato\Catalyst\Console\CatalystSetupCommand;
use Ysato\Catalyst\Console\MetadataSetupCommand;
use Ysato\Catalyst\Console\StandardsSetupCommand;

class CatalystServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->when(ArchitectureSrcSetupCommand::class)
            ->needs(Generator::class)
            ->give(function (Application $app) {
                $fs = $app->make(Filesystem::class);
                $finder = $app->make(Finder::class);
                $temp = TemporaryDirectory::make()
                    ->deleteWhenDestroyed()
                    ->create();
                $tempPath = $temp->path();

                return new Generator($fs, $finder, $temp, __DIR__ . '/stubs/2', $tempPath);
            });

        $this->app->when(StandardsSetupCommand::class)
            ->needs(Generator::class)
            ->give(function (Application $app) {
                $fs = $app->make(Filesystem::class);
                $finder = $app->make(Finder::class);
                $temp = TemporaryDirectory::make()
                    ->deleteWhenDestroyed()
                    ->create();
                $tempPath = $temp->path();

                return new Generator($fs, $finder, $temp, __DIR__ . '/stubs/3', $tempPath);
            });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                CatalystSetupCommand::class,
                MetadataSetupCommand::class,
                ArchitectureSrcSetupCommand::class,
                StandardsSetupCommand::class,
            ]);
        }
    }
}