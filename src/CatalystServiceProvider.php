<?php

declare(strict_types=1);

namespace Ysato\Catalyst;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\ServiceProvider;
use Spatie\TemporaryDirectory\TemporaryDirectory;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use Ysato\Catalyst\Console\ActSetupCommand;
use Ysato\Catalyst\Console\ArchitectureSrcSetupCommand;
use Ysato\Catalyst\Console\CatalystSetupCommand;
use Ysato\Catalyst\Console\ComposerSetupCommand;
use Ysato\Catalyst\Console\GitHubSetupCommand;
use Ysato\Catalyst\Console\IdeSetupCommand;
use Ysato\Catalyst\Console\MetadataSetupCommand;
use Ysato\Catalyst\Console\PhpCsSetupCommand;
use Ysato\Catalyst\Console\PhpMdSetupCommand;
use Ysato\Catalyst\Console\SpectralSetupCommand;

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
                $temp = (new TemporaryDirectory())
                    ->deleteWhenDestroyed()
                    ->create();
                $tempPath = $temp->path();

                return new Generator($fs, $finder, $temp, __DIR__ . '/stubs/architecture-src', $tempPath);
            });

        $this->app->when(PhpCsSetupCommand::class)
            ->needs(Generator::class)
            ->give(function (Application $app) {
                $fs = $app->make(Filesystem::class);
                $finder = $app->make(Finder::class);
                $temp = (new TemporaryDirectory())
                    ->deleteWhenDestroyed()
                    ->create();
                $tempPath = $temp->path();

                return new Generator($fs, $finder, $temp, __DIR__ . '/stubs/phpcs', $tempPath);
            });

        $this->app->when(PhpMdSetupCommand::class)
            ->needs(Generator::class)
            ->give(function (Application $app) {
                $fs = $app->make(Filesystem::class);
                $finder = $app->make(Finder::class);
                $temp = (new TemporaryDirectory())
                    ->deleteWhenDestroyed()
                    ->create();
                $tempPath = $temp->path();

                return new Generator($fs, $finder, $temp, __DIR__ . '/stubs/phpmd', $tempPath);
            });

        $this->app->when(SpectralSetupCommand::class)
            ->needs(Generator::class)
            ->give(function (Application $app) {
                $fs = $app->make(Filesystem::class);
                $finder = $app->make(Finder::class);
                $temp = (new TemporaryDirectory())
                    ->deleteWhenDestroyed()
                    ->create();
                $tempPath = $temp->path();

                return new Generator($fs, $finder, $temp, __DIR__ . '/stubs/spectral', $tempPath);
            });

        $this->app->when(GitHubSetupCommand::class)
            ->needs(Generator::class)
            ->give(function (Application $app) {
                $fs = $app->make(Filesystem::class);
                $finder = $app->make(Finder::class);
                $temp = (new TemporaryDirectory())
                    ->deleteWhenDestroyed()
                    ->create();
                $tempPath = $temp->path();

                return new Generator($fs, $finder, $temp, __DIR__ . '/stubs/.github', $tempPath);
            });

        $this->app->when(IdeSetupCommand::class)
            ->needs(Generator::class)
            ->give(function (Application $app) {
                $fs = $app->make(Filesystem::class);
                $finder = $app->make(Finder::class);
                $temp = (new TemporaryDirectory())
                    ->deleteWhenDestroyed()
                    ->create();
                $tempPath = $temp->path();

                return new Generator($fs, $finder, $temp, __DIR__ . '/stubs/ide', $tempPath);
            });

        $this->app->when(ActSetupCommand::class)
            ->needs(Generator::class)
            ->give(function (Application $app) {
                $fs = $app->make(Filesystem::class);
                $finder = $app->make(Finder::class);
                $temp = (new TemporaryDirectory())
                    ->deleteWhenDestroyed()
                    ->create();
                $tempPath = $temp->path();

                return new Generator($fs, $finder, $temp, __DIR__ . '/stubs/act', $tempPath);
            });

        $this->app->when(ComposerSetupCommand::class)
            ->needs(Generator::class)
            ->give(function (Application $app) {
                $fs = $app->make(Filesystem::class);
                $finder = $app->make(Finder::class);
                $temp = (new TemporaryDirectory())
                    ->deleteWhenDestroyed()
                    ->create();
                $tempPath = $temp->path();

                return new Generator($fs, $finder, $temp, __DIR__ . '/stubs/composer', $tempPath);
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
                PhpCsSetupCommand::class,
                PhpMdSetupCommand::class,
                SpectralSetupCommand::class,
                GitHubSetupCommand::class,
                IdeSetupCommand::class,
                ActSetupCommand::class,
                ComposerSetupCommand::class,
            ]);
        }
    }
}
