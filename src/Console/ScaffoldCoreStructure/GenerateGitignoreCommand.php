<?php

declare(strict_types=1);

namespace Ysato\Catalyst\Console\ScaffoldCoreStructure;

use Illuminate\Console\Command;
use Ysato\Catalyst\Console\Concerns\TaskRenderable;
use Ysato\Catalyst\Generator;

class GenerateGitignoreCommand extends Command
{
    use TaskRenderable;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'catalyst:scaffold-core-structure:generate-gitignore';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate .gitignore file';

    /**
     * @var bool
     */
    protected $hidden = true;

    /**
     * Execute the console command.
     */
    public function handle(Generator $generator)
    {
        /** @noinspection PhpUnhandledExceptionInspection */
        $this->task(function () use ($generator) {
            $ignore = $generator->fs->readFile($this->laravel->basePath('.gitignore'));
            $updated = $this->updateGitIgnore($ignore);

            $generator
                ->dumpFile('.gitignore', $updated)
                ->generate($this->laravel->basePath());
        });

        return 0;
    }

    private function updateGitIgnore(string $contents)
    {
        $before = <<<'EOF'
/.idea/*
!/.idea/codeStyles
!/.idea/fileTemplates
!/.idea/inspectionProfiles

EOF;

        $after = <<<'EOF'
/.actrc
/.php_cs.cache

EOF;

        $patterns = [
            '#^/?.actrc$\R?#m',
            '#^!?/?.idea(/?|/.*)$\R?#m',
            '#^/?.php_cs.cache$\R?#m',
        ];

        $replacements = ['', '', ''];

        $cleaned = preg_replace($patterns, $replacements, $contents);

        return "$before\n$cleaned\n$after";
    }
}
