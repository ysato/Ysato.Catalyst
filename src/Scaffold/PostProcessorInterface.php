<?php

declare(strict_types=1);

namespace Ysato\Catalyst\Scaffold;

use Spatie\TemporaryDirectory\TemporaryDirectory;

interface PostProcessorInterface
{
    public function process(TemporaryDirectory $sandbox): void;
}
