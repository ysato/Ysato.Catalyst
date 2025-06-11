<?php

declare(strict_types=1);

namespace Ysato\Catalyst\Steps;

use Illuminate\Contracts\Foundation\Application;
use Spatie\TemporaryDirectory\TemporaryDirectory;
use Symfony\Component\Filesystem\Filesystem;

class GenerateGitignore implements StepInterface
{
    public function __construct(
        private readonly Filesystem $fs,
        private readonly TemporaryDirectory $sandbox,
        private readonly Application $app
    ) {
    }

    public function execute(): void
    {
        $origin = $this->fs->readFile($this->app->basePath('.gitignore'));
        $content = $this->updateGitIgnore($origin);

        $this->fs->appendToFile($this->sandbox->path('.gitignore'), $content);
    }

    private function updateGitIgnore(string $content)
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
/.phpcs-cache

EOF;

        $patterns = [
            '#^/?.actrc$\R?#m',
            '#^!?/?.idea(/?|/.*)$\R?#m',
            '#^/?.php_cs.cache$\R?#m',
            '#^/?.phpcs-cache$\R?#m',
        ];

        $replacements = ['', '', ''];

        $cleaned = trim(preg_replace($patterns, $replacements, $content));

        return $before . $cleaned . $after;
    }
}
