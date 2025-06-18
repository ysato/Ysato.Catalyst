<?php

declare(strict_types=1);

namespace Ysato\Catalyst\Scaffold;

interface SandboxInterface
{
    public function create(): void;

    public function commit(): void;

    public function delete(): void;

    public function execute(callable $callback): void;
}
