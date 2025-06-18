<?php

declare(strict_types=1);

namespace Ysato\Catalyst\Scaffold;

class Input
{
    public function __construct(
        public readonly string $vendor,
        public readonly string $package,
        public readonly string $php,
        public readonly string|null $caFilePath,
    ) {
    }
}
