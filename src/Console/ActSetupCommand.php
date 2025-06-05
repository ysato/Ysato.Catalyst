<?php

declare(strict_types=1);

namespace Ysato\Catalyst\Console;

use Illuminate\Console\Command;
use Ysato\Catalyst\Generator;

class ActSetupCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'catalyst:act';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Configures local execution for GitHub Actions.';

    /**
     * Execute the console command.
     */
    public function handle(Generator $generator)
    {
        $this->components->info('Setting up...');

        $this->components->task('act', function () use ($generator) {
            $currentIgnore = $generator->fs->readFile($this->laravel->basePath('.gitignore'));

            $generator
                ->dumpFile('.gitignore', $currentIgnore)
                ->appendToFile('.gitignore', ".actrc\n")
                ->generate($this->laravel->basePath());
        });

        return 0;
    }
}