<?php

declare(strict_types=1);

namespace Ysato\Catalyst\OpenApiSpecFollower;

use function strtolower;

readonly class Scenario
{
    public function __construct(public string $path, public string $method, public string $statusCode)
    {
    }

    public function match(string $path, string $method, string $statusCode): bool
    {
        return $this->path === $path
            && strtolower($this->method) === strtolower($method)
            && $this->statusCode === $statusCode;
    }
}
