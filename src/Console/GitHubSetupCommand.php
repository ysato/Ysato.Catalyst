<?php

declare(strict_types=1);

namespace Ysato\Catalyst\Console;

use Illuminate\Console\Command;
use Ysato\Catalyst\Console\Concerns\PhpVersionAskable;
use Ysato\Catalyst\Generator;

class GitHubSetupCommand extends Command
{
    use PhpVersionAskable;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'catalyst:github
                            {php? : Specify the PHP version for the project (e.g., 8.2).}';

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
        $php = $this->getPhpVersionOrAsk();

        $this->components->info('Setting up...');

        $this->components->task('.github', function () use ($php, $generator) {
            $generator
                ->replacePlaceHolder('__Php__', $php)
                ->generate($this->laravel->basePath());
        });

        return 0;
    }
}
