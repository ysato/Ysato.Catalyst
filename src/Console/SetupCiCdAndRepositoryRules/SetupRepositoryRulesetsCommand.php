<?php

declare(strict_types=1);

namespace Ysato\Catalyst\Console\SetupCiCdAndRepositoryRules;

use Illuminate\Console\Command;
use Ysato\Catalyst\Console\Concerns\InputTrait;
use Ysato\Catalyst\Console\Concerns\TaskRenderable;
use Ysato\Catalyst\Generator;

class SetupRepositoryRulesetsCommand extends Command
{
    use InputTrait;
    use TaskRenderable;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'catalyst:setup-ci-cd-and-repository-rules:setup-repository-rulesets
                            {php : Specify the PHP version for the project (e.g., 8.2).}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Setup Repository Rulesets';

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
