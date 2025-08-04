<?php

declare(strict_types=1);

namespace Ysato\Catalyst\OpenApiSpecFollower\Extensions;

use Override;
use PHPUnit\Event\Application\Finished;
use PHPUnit\Event\Application\FinishedSubscriber;
use Ysato\Catalyst\OpenApiSpecFollower\OpenApiSpecFollower;
use Ysato\Catalyst\OpenApiSpecFollower\OpenApiSpecPath;
use Ysato\Catalyst\OpenApiSpecFollower\ResultReporter;

use function env;

class OpenApiSpecFollowReportSubscriber implements FinishedSubscriber
{
    use OpenApiSpecPath;

    #[Override]
    public function notify(Finished $event): void
    {
        unset($event);

        $follower = OpenApiSpecFollower::create($this->getOpenApiSpecPath());

        if (! env('OPENAPI_FOLLOW_REPORT', false)) {
            return;
        }

        $reporter = new ResultReporter($follower->results());
        $reporter->report();
    }
}
