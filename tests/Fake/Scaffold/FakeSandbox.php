<?php

declare(strict_types=1);

namespace Ysato\Catalyst\Scaffold;

use Override;

class FakeSandbox extends Sandbox
{
    #[Override]
    public function delete(): void
    {
    }

    #[Override]
    public function commit(): void
    {
    }
}
