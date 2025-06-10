<?php

declare(strict_types=1);

namespace Ysato\Catalyst\Console\Concerns;

trait PhpVersionAskable
{
    protected function getPhpVersion(): ?string
    {
        return $this->argument('php');
    }
}
