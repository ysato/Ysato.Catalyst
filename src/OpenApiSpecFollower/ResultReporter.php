<?php

declare(strict_types=1);

namespace Ysato\Catalyst\OpenApiSpecFollower;

use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Output\ConsoleOutput;
use Ysato\Catalyst\OpenApiSpecFollower\Result\Implemented;

use function strtoupper;

class ResultReporter
{
    public function __construct(private readonly Results $results)
    {
    }

    public function report(): void
    {
        $output = new ConsoleOutput();
        $table = new Table($output);

        $table->setHeaders(['IMPLEMENTED', 'METHOD', 'ENDPOINT', 'STATUS CODE']);

        foreach ($this->results as $result) {
            $icon = $result instanceof Implemented ? '✅' : '❌';

            $table->addRow([
                $icon,
                strtoupper($result->scenario->method),
                $result->scenario->path,
                $result->scenario->statusCode,
            ]);
        }

        $table->render();
    }
}
