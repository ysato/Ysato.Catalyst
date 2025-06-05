<?php

declare(strict_types=1);

namespace Ysato\Catalyst\Console;

use Illuminate\Console\Command;
use Ysato\Catalyst\Generator;

class GitHubSetupCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'catalyst:github';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Initializes recommended GitHub workflows and rulesets for the project.';

    /**
     * Execute the console command.
     */
    public function handle(Generator $generator)
    {
        $this->components->info('Setting up...');

        $this->components->task('.github', function () use ($generator) {
            $generator->generate($this->laravel->basePath());
        });

        return 0;
    }
}