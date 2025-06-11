<?php

declare(strict_types=1);

namespace Ysato\Catalyst\Console\SetupCiCdAndRepositoryRules;

use Illuminate\Console\Command;
use Ysato\Catalyst\Console\Concerns\InputTrait;
use Ysato\Catalyst\Console\Concerns\TaskRenderable;
use Ysato\Catalyst\Generator;

class GenerateGitHubActionsWorkflowsCommand extends Command
{
    use InputTrait;
    use TaskRenderable;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'catalyst:setup-ci-cd-and-repository-rules:generate-github-actions-workflows
                            {php : Specify the PHP version for the project (e.g., 8.2).}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate GitHub Workflows';

    protected $hidden = true;

    /**
     * Execute the console command.
     */
    public function handle(Generator $generator)
    {
        $php = $this->getPhpVersion();

        /** @noinspection PhpUnhandledExceptionInspection */
        $this->task(function () use ($php, $generator) {
            $generator
                ->replacePlaceHolder('__Php__', $php)
                ->generate($this->laravel->basePath());
        });

        return 0;
    }
}
