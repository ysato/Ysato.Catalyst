<?php

declare(strict_types=1);

namespace Ysato\Catalyst\Scaffold;

use function array_fill;
use function count;
use function file_get_contents;
use function preg_replace;
use function trim;

class ContextFactory
{
    public function createFromProject(
        string $vendor,
        string $package,
        string $php,
        ?string $caFile,
        string $gitignorePath
    ): Context {
        $content = file_get_contents($gitignorePath);
        $originalGitignore = $this->cleanGitignore($content);

        return new Context($vendor, $package, $php, $caFile, $originalGitignore);
    }

    private function cleanGitignore(string $content): string
    {
        $patterns = [
            '#^!?/?.idea(/?|/.*)$\R?#m',
            '#^/?.php_cs.cache$\R?#m',
            '#^/?.phpcs-cache$\R?#m',
        ];

        $replacements = array_fill(0, count($patterns), '');

        return trim(preg_replace($patterns, $replacements, $content));
    }
}
