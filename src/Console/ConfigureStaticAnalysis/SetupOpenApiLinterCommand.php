<?php

declare(strict_types=1);

namespace Ysato\Catalyst\Console\ConfigureStaticAnalysis;

use Illuminate\Console\Command;
use Ysato\Catalyst\Console\Concerns\TaskRenderable;
use Ysato\Catalyst\Generator;

class SetupOpenApiLinterCommand extends Command
{
    use TaskRenderable;

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

    protected $hidden = true;

    /**
     * Execute the console command.
     */
    public function handle(Generator $generator)
    {
        /** @noinspection PhpUnhandledExceptionInspection */
        $this->task(function () use ($generator) {
            $generator->generate($this->laravel->basePath());
        });

        return 0;
    }
}
