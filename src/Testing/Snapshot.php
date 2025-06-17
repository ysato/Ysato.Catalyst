<?php

declare(strict_types=1);

namespace Ysato\Catalyst\Testing;

use Symfony\Component\Filesystem\Filesystem;

use function file_exists;
use function is_dir;

class Snapshot
{
    public function __construct(
        private readonly string $id,
        private readonly string $expectedPath,
        private readonly DriverInterface $driver,
        private readonly Filesystem $fs = new Filesystem(),
    ) {
    }

    public function id(): string
    {
        return $this->id;
    }

    public function exists(): bool
    {
        return file_exists($this->expectedPath) && is_dir($this->expectedPath);
    }

    public function create(string $actualPath): void
    {
        $this->fs->mirror($actualPath, $this->expectedPath);
    }

    public function assertMatches(string $actualPath): void
    {
        $this->driver->match($this->expectedPath, $actualPath);
    }
}
