<?php

declare(strict_types=1);

namespace Ysato\Catalyst\Console;

use Illuminate\Console\Command;
use Ysato\Catalyst\Generator;

class SpectralSetupCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'catalyst:spectral';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Initializes Spectral by setting up its configuration file and recommended rulesets for API linting within the project.';

    /**
     * Execute the console command.
     */
    public function handle(Generator $generator)
    {
        $this->components->info('Setting up...');

        $this->components->task('spectral', function () use ($generator) {
            $generator->generate($this->laravel->basePath());
        });

        return 0;
    }
}