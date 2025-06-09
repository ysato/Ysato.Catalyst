<?php

declare(strict_types=1);

namespace Ysato\Catalyst\Console\SetupLocalDevelopmentEnvironment;

use Illuminate\Console\Command;
use Ysato\Catalyst\Console\Concerns\TaskRenderable;
use Ysato\Catalyst\Console\Concerns\Washable;
use Ysato\Catalyst\Generator;

class InitializeIdeSettingsCommand extends Command
{
    use Washable;
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
        $this->task(function () use ($generator) {
            $ignore = $generator->fs->readFile($this->laravel->basePath('.gitignore'));
            $washed = $this->wash($ignore);

            $contents = <<<'EOF'
/.idea/*
!/.idea/codeStyles
!/.idea/fileTemplates
!/.idea/inspectionProfiles

EOF;

            $generator
                ->dumpFile('.gitignore', $contents)
                ->appendToFile('.gitignore', "\n$washed")
                ->generate($this->laravel->basePath());
        });

        return 0;
    }

    protected function wash(string $contents): string
    {
        return preg_replace(['#^!?/?.idea(/?|/.*)$\R?#m'], [''], $contents);
    }
}
