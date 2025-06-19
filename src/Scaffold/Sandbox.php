<?php

declare(strict_types=1);

namespace Ysato\Catalyst\Scaffold;

use Override;
use Spatie\TemporaryDirectory\TemporaryDirectory;
use Symfony\Component\Filesystem\Filesystem;

class Sandbox implements SandboxInterface
{
    public function __construct(
        private readonly TemporaryDirectory $sandbox,
        private readonly string $basePath,
        private readonly Filesystem $filesystem = new Filesystem(),
    ) {
    }

    #[Override]
    public function create(): void
    {
        $this->sandbox->create();
    }

    #[Override]
    public function commit(): void
    {
        $this->filesystem->mirror($this->sandbox->path(), $this->basePath, options: ['overwrite' => true]);
    }

    #[Override]
    public function delete(): void
    {
        $this->sandbox->delete();
    }

    #[Override]
    public function execute(callable $callback): void
    {
        $callback($this->sandbox->path());
    }
}
