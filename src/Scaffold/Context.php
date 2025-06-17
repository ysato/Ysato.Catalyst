<?php

declare(strict_types=1);

namespace Ysato\Catalyst\Scaffold;

class Context
{
    public function __construct(
        public readonly string $vendor,
        public readonly string $package,
        public readonly string $php,
        public readonly ?string $caFilePath,
        public readonly string $originalGitignore,
    ) {
    }

    public function hasCA(): bool
    {
        return $this->caFilePath !== null;
    }

    /**
     * @return array<string, string|bool>
     */
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
}
