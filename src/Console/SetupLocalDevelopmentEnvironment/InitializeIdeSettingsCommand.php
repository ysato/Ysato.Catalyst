<?php

declare(strict_types=1);

namespace Ysato\Catalyst\Console\SetupLocalDevelopmentEnvironment;

use Illuminate\Console\Command;
use Ysato\Catalyst\Console\Concerns\TaskRenderable;
use Ysato\Catalyst\Generator;

class InitializeIdeSettingsCommand extends Command
{
    use TaskRenderable;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'catalyst:setup-local-development-environment:initialize-ide-settings';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Initialize IDE Settings';

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
