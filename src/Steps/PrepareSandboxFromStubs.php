<?php

declare(strict_types=1);

namespace Ysato\Catalyst\Steps;

use Spatie\TemporaryDirectory\TemporaryDirectory;
use Symfony\Component\Filesystem\Filesystem;

class PrepareSandboxFromStubs implements StepInterface
{
    public function __construct(private readonly Filesystem $fs, private readonly TemporaryDirectory $sandbox)
    {
    }

    public function execute(): void
    {
        $this->fs->mirror(__DIR__ . '/../stubs', $this->sandbox->path(), options: ['override' => true]);
    }
}
