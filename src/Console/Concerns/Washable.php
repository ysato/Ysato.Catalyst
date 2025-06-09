<?php

declare(strict_types=1);

namespace Ysato\Catalyst\Console\Concerns;

trait Washable
{
    abstract protected function wash(string $contents): string;
}
