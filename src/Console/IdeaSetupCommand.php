<?php

declare(strict_types=1);

namespace Ysato\Catalyst\Console;

use Illuminate\Console\Command;
use Ysato\Catalyst\Generator;

class IdeaSetupCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'catalyst:idea';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Initializes recommended PhpStorm settings within the .idea directory.';

    /**
     * Execute the console command.
     */
    public function handle(Generator $generator)
    {
        $this->components->info('Setting up...');

        $this->components->task('.idea', function () use ($generator) {
            $currentIgnore = $generator->fs->readFile($this->laravel->basePath('.gitignore'));

            $generator
                ->dumpFile('.gitignore', $this->ideaIgnore())
                ->appendToFile('.gitignore', $currentIgnore)
                ->generate($this->laravel->basePath());
        });

        return 0;
    }

    private function ideaIgnore(): string
    {
        return <<<'EOF'
/.idea/*
!/.idea/codeStyles
!/.idea/fileTemplates
!/.idea/inspectionProfiles

EOF;
    }
}