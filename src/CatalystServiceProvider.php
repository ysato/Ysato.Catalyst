<?php

declare(strict_types=1);

namespace Ysato\Catalyst;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\ServiceProvider;
use Spatie\TemporaryDirectory\TemporaryDirectory;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use Ysato\Catalyst\Console\ConfigureStaticAnalysis;
use Ysato\Catalyst\Console\NewProjectScaffoldingCommand;
use Ysato\Catalyst\Console\ScaffoldCoreStructure;
use Ysato\Catalyst\Console\SetupCiCdAndRepositoryRules;
use Ysato\Catalyst\Console\SetupLocalDevelopmentEnvironment;

class CatalystServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->when(ScaffoldCoreStructure\InitializeDirectoryArchitectureCommand::class)
            ->needs(Generator::class)
            ->give(function (Application $app) {
                $fs = $app->make(Filesystem::class);
                $finder = $app->make(Finder::class);
                $temp = (new TemporaryDirectory())
                    ->deleteWhenDestroyed()
                    ->create();
                $tempPath = $temp->path();

                return new Generator(
                    $fs,
                    $finder,
                    $temp,
                    __DIR__ . '/stubs/scaffold-core-structure/initialize-directory-architecture',
                    $tempPath
                );
            });

        $this->app->when(ConfigureStaticAnalysis\SetupPhpCodeSnifferCommand::class)
            ->needs(Generator::class)
            ->give(function (Application $app) {
                $fs = $app->make(Filesystem::class);
                $finder = $app->make(Finder::class);
                $temp = (new TemporaryDirectory())
                    ->deleteWhenDestroyed()
                    ->create();
                $tempPath = $temp->path();

                return new Generator(
                    $fs,
                    $finder,
                    $temp,
                    __DIR__ . '/stubs/configure-static-analysis/setup-php-code-sniffer',
                    $tempPath
                );
            });

        $this->app->when(ConfigureStaticAnalysis\SetupPhpMessDetectorCommand::class)
            ->needs(Generator::class)
            ->give(function (Application $app) {
                $fs = $app->make(Filesystem::class);
                $finder = $app->make(Finder::class);
                $temp = (new TemporaryDirectory())
                    ->deleteWhenDestroyed()
                    ->create();
                $tempPath = $temp->path();

                return new Generator(
                    $fs,
                    $finder,
                    $temp,
                    __DIR__ . '/stubs/configure-static-analysis/setup-php-mess-detector',
                    $tempPath
                );
            });

        $this->app->when(ConfigureStaticAnalysis\SetupOpenApiLinterCommand::class)
            ->needs(Generator::class)
            ->give(function (Application $app) {
                $fs = $app->make(Filesystem::class);
                $finder = $app->make(Finder::class);
                $temp = (new TemporaryDirectory())
                    ->deleteWhenDestroyed()
                    ->create();
                $tempPath = $temp->path();

                return new Generator(
                    $fs,
                    $finder,
                    $temp,
                    __DIR__ . '/stubs/configure-static-analysis/setup-openapi-linter',
                    $tempPath
                );
            });

        $this->app->when(SetupCiCdAndRepositoryRules\GenerateGitHubActionsWorkflowsCommand::class)
            ->needs(Generator::class)
            ->give(function (Application $app) {
                $fs = $app->make(Filesystem::class);
                $finder = $app->make(Finder::class);
                $temp = (new TemporaryDirectory())
                    ->deleteWhenDestroyed()
                    ->create();
                $tempPath = $temp->path();

                return new Generator(
                    $fs,
                    $finder,
                    $temp,
                    __DIR__ . '/stubs/setup-ci-cd-and-repository-rules/generate-github-actions-workflows',
                    $tempPath
                );
            });

        $this->app->when(SetupCiCdAndRepositoryRules\SetupRepositoryRulesetsCommand::class)
            ->needs(Generator::class)
            ->give(function (Application $app) {
                $fs = $app->make(Filesystem::class);
                $finder = $app->make(Finder::class);
                $temp = (new TemporaryDirectory())
                    ->deleteWhenDestroyed()
                    ->create();
                $tempPath = $temp->path();

                return new Generator(
                    $fs,
                    $finder,
                    $temp,
                    __DIR__ . '/stubs/setup-ci-cd-and-repository-rules/setup-repository-rulesets',
                    $tempPath
                );
            });

        $this->app->when(SetupCiCdAndRepositoryRules\ConfigureLocalActionRunnerCommand::class)
            ->needs(Generator::class)
            ->give(function (Application $app) {
                $fs = $app->make(Filesystem::class);
                $finder = $app->make(Finder::class);
                $temp = (new TemporaryDirectory())
                    ->deleteWhenDestroyed()
                    ->create();
                $tempPath = $temp->path();

                return new Generator(
                    $fs,
                    $finder,
                    $temp,
                    __DIR__ . '/stubs/setup-ci-cd-and-repository-rules/configure-local-action-runner',
                    $tempPath
                );
            });

        $this->app->when(SetupLocalDevelopmentEnvironment\InitializeIdeSettingsCommand::class)
            ->needs(Generator::class)
            ->give(function (Application $app) {
                $fs = $app->make(Filesystem::class);
                $finder = $app->make(Finder::class);
                $temp = (new TemporaryDirectory())
                    ->deleteWhenDestroyed()
                    ->create();
                $tempPath = $temp->path();

                return new Generator(
                    $fs,
                    $finder,
                    $temp,
                    __DIR__ . '/stubs/setup-local-development-environment/initialize-ide-setting',
                    $tempPath
                );
            });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                NewProjectScaffoldingCommand::class,
                ScaffoldCoreStructure\GenerateComposerMetadataCommand::class,
                ScaffoldCoreStructure\InitializeDirectoryArchitectureCommand::class,
                ConfigureStaticAnalysis\SetupPhpCodeSnifferCommand::class,
                ConfigureStaticAnalysis\SetupPhpMessDetectorCommand::class,
                ConfigureStaticAnalysis\SetupOpenApiLinterCommand::class,
                SetupCiCdAndRepositoryRules\GenerateGitHubActionsWorkflowsCommand::class,
                SetupCiCdAndRepositoryRules\SetupRepositoryRulesetsCommand::class,
                SetupCiCdAndRepositoryRules\ConfigureLocalActionRunnerCommand::class,
                SetupLocalDevelopmentEnvironment\InitializeIdeSettingsCommand::class,
            ]);
        }
    }
}
