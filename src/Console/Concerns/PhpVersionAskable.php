<?php

declare(strict_types=1);

namespace Ysato\Catalyst\Console\Concerns;

trait PhpVersionAskable
{
    private function getPhpVersion(): ?string
    {
        return $this->argument('php');
    }

    private function getPhpVersionOrAsk(): string
    {
        return $this->getPhpVersion() ?? $this->ask('What PHP version does this package require?', '8.2');
    }
}
