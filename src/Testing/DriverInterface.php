<?php

declare(strict_types=1);

namespace Ysato\Catalyst\Testing;

interface DriverInterface
{
    public function match(string $expected, string $actual): void;
}
