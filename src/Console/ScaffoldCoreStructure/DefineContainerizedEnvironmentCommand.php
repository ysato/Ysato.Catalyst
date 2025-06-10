<?php

declare(strict_types=1);

namespace Ysato\Catalyst\Console\ScaffoldCoreStructure;

use Illuminate\Console\Command;
use Ysato\Catalyst\Console\Concerns\PhpVersionAskable;
use Ysato\Catalyst\Console\Concerns\TaskRenderable;
use Ysato\Catalyst\Generator;

class DefineContainerizedEnvironmentCommand extends Command
{
    use PhpVersionAskable;
    use TaskRenderable;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'catalyst:scaffold-core-structure:define-containerized-environment
                            {php : Specify the PHP version for the project (e.g., 8.2).}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Define Containerized Environment';

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
