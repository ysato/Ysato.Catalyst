<?php

declare(strict_types=1);

namespace Ysato\Catalyst\Console\Concerns;

trait PhpVersionAskable
{
    protected function getPhpVersionOrAsk(): string
    {
        return $this->argument('php') ?? $this->ask('What PHP version does this package require?', '8.2');
    }
}
