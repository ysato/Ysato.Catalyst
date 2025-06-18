<?php

declare(strict_types=1);

namespace Ysato\Catalyst\Scaffold;

use function array_fill;
use function count;
use function file_get_contents;
use function preg_replace;
use function trim;

class Context
{
    public function __construct(
        public readonly string $vendor,
        public readonly string $package,
        public readonly string $php,
        public readonly string|null $caFilePath,
        public readonly string $originalGitignore,
    ) {
    }

    public static function fromInputAndGitignorePath(Input $input, string $gitignorePath): self
    {
        $content = file_get_contents($gitignorePath);
        $originalGitignore = self::cleanGitignore($content);

        return new self($input->vendor, $input->package, $input->php, $input->caFilePath, $originalGitignore);
    }

    public function hasCA(): bool
    {
        return $this->caFilePath !== null;
    }

    /** @return array<string, string|bool> */
    public function toArray(): array
    {
        return [
            'vendor' => $this->vendor,
            'package' => $this->package,
            'php' => $this->php,
            'with_ca' => $this->caFilePath,
            'gitignore_content' => $this->originalGitignore,
            'original_gitignore' => $this->originalGitignore,
            'has_ca' => $this->hasCA(),
        ];
    }

    private static function cleanGitignore(string $content): string
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
