<?php

declare(strict_types=1);

namespace Ysato\Catalyst\Console\ConfigureStaticAnalysis;

use Illuminate\Console\Command;
use Ysato\Catalyst\Generator;

class SetupOpenAPILinterCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'catalyst:configure-static-analysis:setup-openapi-linter';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Setup Spectral';

    /**
     * Execute the console command.
     */
    public function handle(Generator $generator)
    {
        $this->task('spectral', function () use ($generator) {
            $generator->generate($this->laravel->basePath());
        });

        return 0;
    }
}
