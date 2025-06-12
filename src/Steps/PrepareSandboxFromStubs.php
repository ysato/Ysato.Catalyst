<?php

declare(strict_types=1);

namespace Ysato\Catalyst\Steps;

use Spatie\TemporaryDirectory\TemporaryDirectory;
use Symfony\Component\Filesystem\Filesystem;

class PrepareSandboxFromStubs implements StepInterface
{
    public function __construct(
        private readonly Filesystem $fs,
        private readonly TemporaryDirectory $sandbox,
        private readonly string $stubsPath
    ) {
    }

    public function execute(): void
    {
        $this->fs->mirror($this->stubsPath, $this->sandbox->path(), options: ['override' => true]);
    }
}
