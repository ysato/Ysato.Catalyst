<?php

declare(strict_types=1);

namespace Ysato\Catalyst\OpenApiSpecFollower\Result;

use Ysato\Catalyst\OpenApiSpecFollower\Scenario;

abstract class Result
{
    public function __construct(public readonly Scenario $scenario)
    {
    }
}
