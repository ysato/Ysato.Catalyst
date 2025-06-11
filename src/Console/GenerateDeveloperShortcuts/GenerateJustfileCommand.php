<?php

declare(strict_types=1);

namespace Ysato\Catalyst\Console\GenerateDeveloperShortcuts;

use Illuminate\Console\Command;
use Ysato\Catalyst\Console\Concerns\InputTrait;
use Ysato\Catalyst\Console\Concerns\TaskRenderable;
use Ysato\Catalyst\Generator;

class GenerateJustfileCommand extends Command
{
    use InputTrait;
    use TaskRenderable;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'catalyst:generate-developer-shortcuts:generate-justfile
                            {php : Specify the PHP version for the project (e.g., 8.2).}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate justfile';

    /**
     * @var bool
     */
    protected $hidden = true;

    /**
     * Execute the console command.
     */
    public function handle(Generator $generator)
    {
        $php = $this->getValidatedPhpVersion();

        /** @noinspection PhpUnhandledExceptionInspection */
        $this->task(function () use ($generator, $php) {
            $generator
                ->replacePlaceHolder('__Php__', $php)
                ->generate($this->laravel->basePath());
        });

        return 0;
    }
}
