<?php

declare(strict_types=1);

namespace Ysato\Catalyst\Console;

use Illuminate\Console\Command;
use Ysato\Catalyst\Console\Concerns\Washable;
use Ysato\Catalyst\Generator;

class IdeSetupCommand extends Command
{
    use Washable;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'catalyst:ide';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Initializes recommended IDE and code style settings for the project.';

    /**
     * Execute the console command.
     */
    public function handle(Generator $generator)
    {
        $this->components->info('Setting up...');

        $this->components->task('ide', function () use ($generator) {
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
