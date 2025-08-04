<?php

declare(strict_types=1);

namespace Ysato\Catalyst\OpenApiSpecFollower;

use ArrayIterator;
use IteratorAggregate;
use Override;
use Ysato\Catalyst\OpenApiSpecFollower\Result\Implemented;
use Ysato\Catalyst\OpenApiSpecFollower\Result\NotImplemented;
use Ysato\Catalyst\OpenApiSpecFollower\Result\Result;

/** @implements IteratorAggregate<int, Result> */
class Results implements IteratorAggregate
{
    /** @param Result[] $results */
    public function __construct(private array $results = [])
    {
    }

    public function implemented(string $method, string $path, string $statusCode): void
    {
        $results = [];
        foreach ($this->results as $result) {
            if ($result->scenario->match($path, $method, $statusCode)) {
                $result = new Implemented($result->scenario);
            }

            $results[] = $result;
        }

        $this->results = $results;
    }

    public function addScenario(Scenario $scenario): void
    {
        $this->results[] = new NotImplemented($scenario);
    }

    /** @return ArrayIterator<array-key, Result> */
    #[Override]
    public function getIterator(): ArrayIterator
    {
        return new ArrayIterator($this->results);
    }
}
