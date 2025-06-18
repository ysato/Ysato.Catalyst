<?php

declare(strict_types=1);

namespace Ysato\Catalyst\Scaffold;

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

    public function create(): void
    {
        $this->sandbox->create();
    }

    public function commit(): void
    {
        $this->filesystem->mirror($this->sandbox->path(), $this->basePath, options: ['overwrite' => true]);
    }

    public function delete(): void
    {
        $this->sandbox->delete();
    }

    public function execute(callable $callback): void
    {
        $callback($this->sandbox->path());
    }
}
